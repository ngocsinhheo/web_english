const fs = require('fs');
const path = require('path');

const directoryPath = 'C:/xampp/htdocs/web_english'; // Thư mục gốc
const outputFile = 'full_code_list.txt'; // Tệp đầu ra

// Danh sách phần mở rộng tệp mã nguồn cần xuất
const codeExtensions = ['.php', '.js', '.css', '.html', '.csv', '.txt'];

// Hàm kiểm tra xem tệp có phải là mã nguồn không
function isCodeFile(file) {
    return codeExtensions.includes(path.extname(file).toLowerCase());
}

// Hàm liệt kê và xuất nội dung tệp
function exportCodeFiles(dir) {
    const files = fs.readdirSync(dir);
    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            if (file !== '.git') { // Bỏ qua thư mục .git
                fs.appendFileSync(outputFile, `\n[Thư mục] ${filePath}\n`);
                exportCodeFiles(filePath); // Đệ quy vào thư mục con
            }
        } else if (isCodeFile(file)) { // Chỉ xử lý tệp mã nguồn
            fs.appendFileSync(outputFile, `\n[Tệp] ${filePath}\n`);
            try {
                const fileContent = fs.readFileSync(filePath, 'utf8');
                fs.appendFileSync(outputFile, `Nội dung:\n${fileContent}\n`);
            } catch (err) {
                fs.appendFileSync(outputFile, `Không thể đọc nội dung: ${err.message}\n`);
            }
        }
    });
}

// Xóa tệp đầu ra cũ (nếu có) và bắt đầu xuất
if (fs.existsSync(outputFile)) fs.unlinkSync(outputFile);
exportCodeFiles(directoryPath);
console.log(`Đã xuất đầy đủ mã nguồn vào ${outputFile}!`);