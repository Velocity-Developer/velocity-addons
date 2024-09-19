const fs = require("fs-extra");
const archiver = require("archiver");
const path = require("path");
const glob = require("glob");
const packageJson = require("../package.json");

// Nama folder dan file zip yang akan dibuat
const pluginName = packageJson.name;
const pluginVersion = packageJson.version;
const outputFolder = path.join(__dirname, "../dist");
const tempFolder = path.join(outputFolder, pluginName);
const outputFileName = `${pluginName}-${pluginVersion}.zip`;
const outputPath = path.join(outputFolder, outputFileName);

// Fungsi untuk menghapus folder
const clearFolder = (folderPath) => {
  if (fs.existsSync(folderPath)) {
    fs.rmSync(folderPath, { recursive: true, force: true });
  }
};

// Pastikan folder 'dist' ada dan kosongkan isinya
if (!fs.existsSync(outputFolder)) {
  fs.mkdirSync(outputFolder);
} else {
  clearFolder(outputFolder);
}

// Hapus folder sementara jika sudah ada
if (fs.existsSync(tempFolder)) {
  clearFolder(tempFolder);
}

// Buat folder sementara berdasarkan nama plugin
fs.mkdirSync(tempFolder, { recursive: true });

// Salin file ke dalam folder sementara
const files = glob.sync("**/*", {
  cwd: path.join(__dirname, ".."),
  ignore: [
    "dist/**", // Abaikan folder dist
    "src/**", // Abaikan folder src
    "node_modules/**", // Abaikan folder node_modules
    "package.json", // Abaikan file package.json
    "package-lock.json", // Abaikan file package-lock.json
  ],
});

files.forEach((file) => {
  const filePath = path.join(__dirname, "..", file);
  const destPath = path.join(tempFolder, file);

  // Salin file atau folder
  fs.copySync(filePath, destPath);
});

// Membuat file output stream
const output = fs.createWriteStream(outputPath);
const archive = archiver("zip", {
  zlib: { level: 9 }, // Compression level
});

// Event listener saat proses selesai
output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log("File zip telah dibuat: " + outputFileName);

  // Hapus folder sementara setelah zip selesai
  clearFolder(tempFolder);
});

// Event listener saat error
archive.on("error", function (err) {
  throw err;
});

// Mulai membuat file zip
archive.pipe(output);

// Tambahkan folder sementara ke arsip zip dengan nama folder di dalam zip
archive.directory(tempFolder, pluginName);

// Akhiri proses zip
archive.finalize();
