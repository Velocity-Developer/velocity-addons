const fs = require("fs");
const archiver = require("archiver");
const path = require("path");
const packageJson = require("../package.json");

// Nama folder dan file zip yang akan dibuat
const pluginName = "velocity-addons";
const pluginVersion = packageJson.version;
const outputFolder = path.join(__dirname, "../dist");
const outputFileName = `${pluginName}-${pluginVersion}.zip`;
const outputPath = path.join(outputFolder, outputFileName);

// Pastikan folder 'dist' ada
if (!fs.existsSync(outputFolder)) {
  fs.mkdirSync(outputFolder);
}

// Membuat file output stream
const output = fs.createWriteStream(outputPath);
const archive = archiver("zip", {
  zlib: { level: 9 }, // Compression level
});

// Event listener saat proses selesai
output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log("File zip telah dibuat: " + outputFileName);
});

// Event listener saat error
archive.on("error", function (err) {
  throw err;
});

// Mulai membuat file zip
archive.pipe(output);

// Tambahkan semua file dalam direktori plugin ke zip
archive.directory(__dirname, false);

// Akhiri proses zip
archive.finalize();
