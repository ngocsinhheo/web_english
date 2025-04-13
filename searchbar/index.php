<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu từ điển Anh-Việt</title>
    <link rel="stylesheet" href="../searchbar/css/style.css">
</head>
<body>
    <!-- Khối chứa biểu tượng và bảng tra -->
    <div id="search-wrapper" class="search-wrapper">
        <!-- Biểu tượng tìm kiếm -->
        <div id="search-icon" class="search-icon">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
        <!-- Bảng tra -->
        <div id="search-bar" class="search-bar hidden">
            <input type="text" id="search-input" placeholder="Nhập từ cần tra...">
            <select id="language">
                <option value="en">Anh → Việt</option>
                <option value="vi">Việt → Anh</option>
            </select>
            <button onclick="searchWord()">Tra cứu</button>
            <!-- Kết quả tìm kiếm -->
            <div id="result" class="result"></div>
        </div>
    </div>

    <!-- Bảng chi tiết -->
    <div id="detail-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div id="detail-content"></div>
        </div>
    </div>

    <script src="../searchbar/js/script.js"></script>
</body>
</html>