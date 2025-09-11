const { app, BrowserWindow } = require('electron');
const { exec, execSync } = require('child_process');
const path = require('path');
const net = require('net');

let phpServer;
let mariadbServer;
let mainWindow;
let loadingWindow;

// ✅ Deteksi mode dev atau production
const isDev = !app.isPackaged;

// 🔹 Path build (html, css, js)
const buildPath = isDev
  ? path.join(__dirname, 'build')       // saat npm start
  : path.join(app.getAppPath(), 'build'); // saat dist (app.asar)

// 🔹 Path php & mariadb
const resourcesPath = isDev
  ? path.join(__dirname, 'resources')  // saat npm start
  : process.resourcesPath;             // saat dist

// 🔍 Fungsi cek port apakah available
function checkPort(port, host = '127.0.0.1') {
  return new Promise((resolve) => {
    const server = net.createServer()
      .once('error', () => resolve(false))
      .once('listening', () => {
        server.close();
        resolve(true);
      })
      .listen(port, host);
  });
}

app.on('ready', async () => {
  // 🔹 Loading window
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
  const phpPath = path.join(resourcesPath, 'php', 'php.exe');
  const phpIniPath = path.join(resourcesPath, 'php', 'php.ini');
  const mariadbPath = path.join(resourcesPath, 'mariadb', 'bin', 'mysqld.exe');
  const myIniPath = path.join(resourcesPath, 'mariadb', 'my.ini');

  // 🔹 Start MariaDB
  const mariadbCommand = `"${mariadbPath}" --defaults-file="${myIniPath}" --standalone --console --port=3307`;
  mariadbServer = exec(mariadbCommand, { cwd: path.join(resourcesPath, 'mariadb') });

  // 🔹 Cari port kosong mulai dari 8080
  let phpPort = 8080;
  while (!(await checkPort(phpPort))) {
    phpPort++;
  }

  // 🔹 Start PHP server
  const phpCommand = `"${phpPath}" -c "${phpIniPath}" -S localhost:${phpPort} -t "${buildPath}"`;
  phpServer = exec(phpCommand);

  // 🔹 Window utama
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
    mainWindow.maximize();
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
      const mysqladminPath = path.join(resourcesPath, 'mariadb', 'bin', 'mysqladmin.exe');
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