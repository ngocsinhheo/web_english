const fs = require('fs');
const path = require('path');

// Cấu hình
const projectDir = path.resolve('C:/xampp/htdocs/web_english');
const outputFile = 'toan_bo_code_va_cay_thu_muc.txt';

// Phần mở rộng cần XUẤT CODE (chỉ những loại này mới đọc nội dung file)
const allowedExtensionsForCode = ['.html', '.css', '.php', '.js'];

// Thư mục không cần scan
const excludedFolders = ['node_modules', '.git', 'vendor', 'storage', 'build', 'dist'];

const output = fs.createWriteStream(outputFile, { encoding: 'utf-8' });

// Hàm đệ quy duyệt thư mục và in cây + mã nguồn phù hợp
function exportTree(dir, depth = 0) {
  const indent = '  '.repeat(depth);
  let items = [];

  try {
    items = fs.readdirSync(dir, { withFileTypes: true });
  } catch (err) {
    output.write(`${indent}❌ Không thể đọc thư mục: ${dir}\n`);
    return;
  }

  items.forEach((item) => {
    const fullPath = path.join(dir, item.name);
    const relativePath = path.relative(projectDir, fullPath);
    const ext = path.extname(item.name).toLowerCase();

    // Nếu là thư mục
    if (item.isDirectory()) {
      if (!excludedFolders.includes(item.name)) {
        output.write(`${indent}- 📁 ${item.name}/\n`);
        exportTree(fullPath, depth + 1);
      }
    } else {
      // In ra tất cả các file
      output.write(`${indent}- 📄 ${item.name}\n`);

      // Chỉ xuất code nếu là file hợp lệ
      if (allowedExtensionsForCode.includes(ext)) {
        try {
          const content = fs.readFileSync(fullPath, 'utf-8');
          output.write(`\n${indent}----- 📄 Nội dung file: ${relativePath} -----\n`);
          output.write(content + '\n');
          output.write(`${indent}----- 🔚 Hết file: ${relativePath} -----\n\n`);
        } catch (err) {
          output.write(`${indent}❌ Lỗi đọc file: ${relativePath}\n`);
        }
      }
    }
  });
}

// Bắt đầu
output.write(`📦 Cấu trúc thư mục & mã nguồn: ${projectDir}\n\n`);
exportTree(projectDir);
output.end(() => {
  console.log(`✅ Xuất thành công ra file: ${outputFile}`);
});
