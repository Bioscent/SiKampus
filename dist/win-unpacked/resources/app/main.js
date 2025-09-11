const { app, BrowserWindow } = require('electron');
const { exec, execSync } = require('child_process');
const path = require('path');
const net = require('net');

let phpServer;
let mariadbServer;
let mainWindow;
let loadingWindow;

// ✅ Deteksi mode dev/production
const isDev = process.env.NODE_ENV !== 'production';
const basePath = isDev ? __dirname : process.resourcesPath;

// 🔍 Fungsi cek port apakah available
function checkPort(port, host = '127.0.0.1') {
  return new Promise((resolve) => {
    const server = net.createServer()
      .once('error', () => resolve(false)) // port kepakai
      .once('listening', () => {
        server.close();
        resolve(true); // port kosong
      })
      .listen(port, host);
  });
}

// 🔍 Fungsi tunggu port benar-benar ready
function waitForPort(port, host = '127.0.0.1', timeout = 20000) {
  return new Promise((resolve, reject) => {
    const start = Date.now();
    function tryConnect() {
      const socket = new net.Socket();
      socket.setTimeout(1000);

      socket.once('connect', () => {
        socket.destroy();
        resolve();
      });

      socket.once('timeout', () => {
        socket.destroy();
        if (Date.now() - start > timeout) {
          reject(new Error(`Timeout menunggu port ${port}`));
        } else {
          setTimeout(tryConnect, 500);
        }
      });

      socket.once('error', () => {
        socket.destroy();
        if (Date.now() - start > timeout) {
          reject(new Error(`Gagal konek ke port ${port}`));
        } else {
          setTimeout(tryConnect, 500);
        }
      });

      socket.connect(port, host);
    }
    tryConnect();
  });
}

app.on('ready', async () => {
  const buildPath = path.join(basePath, 'build');

  // 🔹 Loading window (opsional, kalau mau skip bisa hapus)
  loadingWindow = new BrowserWindow({
    width: 400,
    height: 250,
    frame: false,
    resizable: false,
    transparent: true,
    alwaysOnTop: true,
    webPreferences: { nodeIntegration: true }
  });
  loadingWindow.loadFile(path.join(buildPath, 'loading.html'));
  loadingWindow.center();

  // Lokasi PHP & MariaDB
  const phpPath = path.join(basePath, 'php', 'php.exe');
  const phpIniPath = path.join(basePath, 'php', 'php.ini');
  const mariadbPath = path.join(basePath, 'mariadb', 'bin', 'mysqld.exe');
  const myIniPath = path.join(basePath, 'mariadb', 'my.ini');

  // 🔹 Start MariaDB
  const mariadbCommand = `"${mariadbPath}" --defaults-file="${myIniPath}" --standalone --console --port=3307`;
  mariadbServer = exec(mariadbCommand, { cwd: path.join(basePath, 'mariadb') });

  // 🔹 Cari port kosong mulai dari 8080
  let phpPort = 8080;
  while (!(await checkPort(phpPort))) {
    phpPort++;
  }

  // 🔹 Start PHP server
  const phpCommand = `"${phpPath}" -c "${phpIniPath}" -S localhost:${phpPort} -t "${buildPath}"`;
  phpServer = exec(phpCommand);

  // 🚀 Langsung buka window utama tanpa tunggu server siap
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 720,
    show: false,
    webPreferences: { nodeIntegration: false }
  });

  mainWindow.loadURL(`http://localhost:${phpPort}/index.php`);

  mainWindow.once('ready-to-show', () => {
    if (loadingWindow) {
      loadingWindow.close();
      loadingWindow = null;
    }

    // 🔹 Baru maximize setelah loading window ditutup
    mainWindow.maximize();
    mainWindow.show();
  });

  mainWindow.once('ready-to-show', () => {
    if (loadingWindow) {
      loadingWindow.close();
      loadingWindow = null;
    }
    mainWindow.show();
  });
});

app.on('will-quit', () => {
  if (phpServer) {
    phpServer.kill();
    console.log('PHP server dimatikan.');
  }

  if (mariadbServer) {
    try {
      const mysqladminPath = path.join(basePath, 'mariadb', 'bin', 'mysqladmin.exe');
      execSync(`"${mysqladminPath}" -u root --port=3307 shutdown`);
      console.log('MariaDB dimatikan.');
    } catch (err) {
      console.error('Gagal mematikan MariaDB:', err.message);
    }
  }
});

process.on('uncaughtException', (err) => {
  console.error('Uncaught Exception:', err);
});