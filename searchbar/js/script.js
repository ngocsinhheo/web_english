// Kiểm tra xem searchWrapper đã được khai báo chưa
if (typeof searchWrapper === 'undefined') {
    let searchWrapper = document.getElementById('search-wrapper');
    let isDragging = false;
    let currentX, currentY, initialX, initialY;

    // Hàm kéo thả
    function startDragging(e) {
        // Chỉ kéo khi nhấn vào search-icon hoặc khu vực trống của search-bar
        if (e.target.closest('#search-input') || e.target.closest('select') || e.target.closest('button')) return;
        initialX = e.clientX - currentX;
        initialY = e.clientY - currentY;
        isDragging = true;
        e.preventDefault(); // Ngăn hành vi mặc định
    }

    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            searchWrapper.style.left = currentX + 'px';
            searchWrapper.style.top = currentY + 'px';
        }
    }

    function stopDragging() {
        isDragging = false;
    }

    // Gắn sự kiện kéo thả
    searchWrapper.addEventListener('mousedown', startDragging);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDragging);

    // Lưu vị trí và trạng thái ẩn/hiện
    window.addEventListener('beforeunload', () => {
        localStorage.setItem('searchPos', JSON.stringify({
            x: searchWrapper.style.left,
            y: searchWrapper.style.top
        }));
        localStorage.setItem('searchBarHidden', document.getElementById('search-bar').classList.contains('hidden'));
    });

    // Khôi phục vị trí và trạng thái
    window.addEventListener('load', () => {
        let pos = localStorage.getItem('searchPos');
        if (pos) {
            pos = JSON.parse(pos);
            searchWrapper.style.left = pos.x;
            searchWrapper.style.top = pos.y;
        } else {
            searchWrapper.style.left = '20px';
            searchWrapper.style.top = '20px';
        }
        currentX = parseInt(searchWrapper.style.left) || 20;
        currentY = parseInt(searchWrapper.style.top) || 20;

        let isBarHidden = localStorage.getItem('searchBarHidden') === 'true';
        let searchBar = document.getElementById('search-bar');
        let searchIcon = document.getElementById('search-icon');
        searchBar.classList.toggle('hidden', isBarHidden);
        searchIcon.classList.toggle('hidden', !isBarHidden);
    });

    // Nhấn bảng tra để thu gọn thành biểu tượng
    document.getElementById('search-bar').addEventListener('click', (e) => {
        if (!e.target.closest('#search-input') && !e.target.closest('select') && !e.target.closest('button') && !e.target.closest('.result')) {
            let searchBar = document.getElementById('search-bar');
            let searchIcon = document.getElementById('search-icon');
            searchBar.classList.add('hidden');
            searchIcon.classList.remove('hidden');
        }
    });

    // Nhấn biểu tượng để hiện bảng tra
    document.getElementById('search-icon').addEventListener('click', () => {
        let searchBar = document.getElementById('search-bar');
        let searchIcon = document.getElementById('search-icon');
        searchBar.classList.remove('hidden');
        searchIcon.classList.add('hidden');
    });
}

// Tìm kiếm từ
function searchWord() {
    let word = document.getElementById('search-input').value.trim();
    let language = document.getElementById('language').value;

    if (!word) {
        alert('Vui lòng nhập từ cần tra!');
        return;
    }

    fetch(`../searchbar/search.php?word=${encodeURIComponent(word)}&lang=${language}`)
        .then(response => response.json())
        .then(data => {
            let resultDiv = document.getElementById('result');
            if (data.error) {
                resultDiv.innerHTML = `<p>${data.error}</p>`;
            } else {
                resultDiv.innerHTML = `
                    <h3>${data.word}</h3>
                    <p><strong>Nghĩa:</strong> ${data.translation}</p>
                    <p><strong>Loại từ:</strong> ${data.word_type || 'Không có'}</p>
                    <button onclick="speak('${data.word}')">Nghe</button>
                    <button onclick="showDetail(${data.id})">Xem chi tiết</button>
                `;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Đọc từ
function speak(text) {
    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = document.getElementById('language').value === 'en' ? 'en-US' : 'vi-VN';
    speechSynthesis.speak(utterance);
}

// Hiển thị chi tiết
function showDetail(id) {
    fetch(`../searchbar/detail.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            let modal = document.getElementById('detail-modal');
            let content = document.getElementById('detail-content');
            content.innerHTML = `
                <h2>${data.word}</h2>
                <p><strong>Phiên âm:</strong> ${data.pronunciation || 'Không có'}</p>
                <p><strong>Nghĩa:</strong> ${data.translation}</p>
                <p><strong>Loại từ:</strong> ${data.word_type || 'Không có'}</p>
                <p><strong>Giải thích:</strong> ${data.detailed_explanation || 'Không có'}</p>
                <p><strong>Ví dụ:</strong> ${data.example || 'Không có'}</p>
            `;
            modal.style.display = 'block';
        })
        .catch(error => console.error('Error:', error));
}

// Đóng modal
function closeModal() {
    document.getElementById('detail-modal').style.display = 'none';
}