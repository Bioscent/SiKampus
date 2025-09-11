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

// 🔍 Fungsi cek port
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

  // 🔹 Buat loading window dulu
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

  // Lokasi PHP portable
  const phpPath = path.join(basePath, 'php', 'php.exe');
  const phpIniPath = path.join(basePath, 'php', 'php.ini');

  // Lokasi MariaDB portable
  const mariadbPath = path.join(basePath, 'mariadb', 'bin', 'mysqld.exe');
  const myIniPath = path.join(basePath, 'mariadb', 'my.ini');

  // Jalankan MariaDB di port 3307
  const mariadbCommand = `"${mariadbPath}" --defaults-file="${myIniPath}" --standalone --console --port=3307`;
  mariadbServer = exec(mariadbCommand, { cwd: path.join(basePath, 'mariadb') });

  mariadbServer.stdout?.on('data', (data) => console.log(`MariaDB: ${data}`));
  mariadbServer.stderr?.on('data', (data) => console.error(`MariaDB Error: ${data}`));

  waitForPort(3307).then(() => {
    console.log("✅ MariaDB siap!");
  }).catch((err) => {
    console.error("⚠️ MariaDB gagal start:", err.message);
  });

  // ✅ Jalankan PHP server
  const phpCommand = `"${phpPath}" -c "${phpIniPath}" -S localhost:8080 -t "${buildPath}"`;
  console.log('Menjalankan server PHP:', phpCommand);

  phpServer = exec(phpCommand);
  phpServer.stdout?.on('data', (data) => console.log(`PHP Server: ${data}`));
  phpServer.stderr?.on('data', (data) => console.error(`PHP Error: ${data}`));

  // ✅ Tunggu MariaDB & PHP siap → lalu tampilkan window utama
  try {
    await waitForPort(3307);   // tunggu MariaDB dulu
    console.log("✅ MariaDB siap!");
    await waitForPort(8080);   // lalu tunggu PHP
    console.log('✅ PHP server siap!');

    mainWindow = new BrowserWindow({
      width: 1280,
      height: 720,
      show: false,
      webPreferences: { nodeIntegration: false }
    });

    mainWindow.maximize();
    mainWindow.loadURL('http://localhost:8080/index.php');

    mainWindow.once('ready-to-show', () => {
      if (loadingWindow) {
        loadingWindow.close();
        loadingWindow = null;
      }
      mainWindow.show();
    });
  } catch (err) {
    console.error('⚠️ Startup gagal:', err.message);
    if (loadingWindow) {
      loadingWindow.close();
      loadingWindow = null;
    }
    mainWindow = new BrowserWindow({ width: 1280, height: 720 });
    mainWindow.maximize();
    mainWindow.loadURL('http://localhost:8080/index.php');
  }
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