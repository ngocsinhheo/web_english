document.addEventListener('DOMContentLoaded', () => {
    const questionSection = document.getElementById('questionSection');
    const questionList = document.getElementById('questionList');
    const timeDisplay = document.getElementById('timeDisplay');
    const submitBtn = document.getElementById('submitBtn');
    const exitBtn = document.getElementById('exitBtn');
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    const partButtons = document.querySelectorAll('.part-btn');
    const scoreDisplay = document.getElementById('scoreDisplay');
    const audioPlayer = document.getElementById('audioPlayer');
    const audioSource = document.getElementById('audioSource');
    const testTitle = document.getElementById('testTitle');

    let currentPart = 1;
    let timeLeft = 120 * 60;
    let timerInterval;
    let questions = {};
    let currentTest = new URLSearchParams(window.location.search).get('test') || localStorage.getItem('currentTest') || 'test1';

    // Cập nhật tiêu đề
    testTitle.textContent = currentTest.replace('test', '');

    // Tải câu hỏi từ CSV
    async function loadQuestions() {
        try {
            const response = await fetch(`get_questions.php?test=${currentTest}`);
            const data = await response.json();

            if (data.error) {
                console.error('Lỗi từ server:', data.error);
                alert('Lỗi khi tải câu hỏi: ' + data.error);
                return;
            }

            questions = data.reduce((acc, q) => {
                const part = parseInt(q.part);
                if (!acc[part]) acc[part] = [];
                acc[part].push(q);
                return acc;
            }, {});

            console.log(`Dữ liệu câu hỏi cho ${currentTest}:`, questions);

            if (questions[currentPart]) {
                displayQuestions(currentPart);
            } else {
                questionSection.innerHTML = '<p>Không có câu hỏi nào cho phần này.</p>';
            }
        } catch (error) {
            console.error('Lỗi khi tải câu hỏi:', error);
            alert('Không thể tải câu hỏi từ server. Vui lòng kiểm tra kết nối hoặc file CSV.');
        }
    }

    // Khởi động đồng hồ
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timeDisplay.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                alert('Hết giờ! Bài thi của bạn sẽ được nộp.');
                submitTest();
            }
        }, 1000);
    }

    // Cập nhật audio theo part
    function updateAudio(part) {
        const audioFiles = {
            2: `tests/${currentTest}/audio/part2.mp3`,
            3: `tests/${currentTest}/audio/part3.mp3`,
            4: `tests/${currentTest}/audio/part4.mp3`
        };
        const audioSrc = audioFiles[part] || '';
        if (audioSrc) {
            audioSource.src = audioSrc;
            audioPlayer.load();
            audioPlayer.play().catch(error => console.error('Lỗi phát audio:', error));
        } else {
            audioPlayer.pause();
            audioSource.src = '';
        }
    }

    // Hiển thị câu hỏi
    function displayQuestions(part) {
        questionSection.innerHTML = '';
        const partQuestions = questions[part] || [];
        if (partQuestions.length === 0) {
            questionSection.innerHTML = '<p>Không có câu hỏi nào cho phần này.</p>';
            return;
        }

        updateAudio(part);

        if (part == 6 || part == 7) {
            let passageIndex = 0;
            let passageQuestions = [];
            partQuestions.forEach((q, index) => {
                if (index % (part == 6 ? 4 : 5) === 0 && index > 0) {
                    displayPassage(passageIndex, passageQuestions);
                    passageQuestions = [];
                    passageIndex++;
                }
                passageQuestions.push(q);
            });
            if (passageQuestions.length) displayPassage(passageIndex, passageQuestions);
        } else {
            partQuestions.forEach(q => {
                const div = document.createElement('div');
                div.classList.add('question');
                div.innerHTML = `<p>Câu ${q.id}:</p>`;
                if (q.image && q.image.trim() !== '') {
                    const imgPath = `tests/${currentTest}/img/${q.image}`;
                    console.log(`Đường dẫn hình ảnh (Part ${part}, Câu ${q.id}):`, imgPath);
                    div.innerHTML += `<img src="${imgPath}" alt="Question ${q.id}" loading="lazy" onerror="this.src='tests/${currentTest}/img/placeholder.jpg'; this.alt='Hình ảnh không tải được';">`;
                }
                if (q.content && q.content.trim() !== '') {
                    div.innerHTML += `<p>${q.content}</p>`;
                }
                const options = q.type === 'part2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                options.forEach(opt => {
                    if (q[`option${opt}`] && q[`option${opt}`].trim() !== '') {
                        div.innerHTML += `
                            <div class="option">
                                <input type="radio" name="q${q.id}" value="${opt}" id="q${q.id}-${opt}">
                                <label for="q${q.id}-${opt}">${opt}</label>
                            </div>`;
                    }
                });
                questionSection.appendChild(div);
            });
        }
        updateQuestionList(partQuestions);
        loadAnswers();
    }

    function displayPassage(index, questions) {
        const div = document.createElement('div');
        div.classList.add('passage');
        const passageContent = questions[0].passage || '[Nội dung đoạn văn không có]';
        div.innerHTML = `<p class="passage-content">${passageContent}</p>`;
        questions.forEach(q => {
            const qDiv = document.createElement('div');
            qDiv.classList.add('question');
            qDiv.innerHTML = `<p>Câu ${q.id}: ${q.content || ''}</p>`;
            if (q.image && q.image.trim() !== '') {
                const imgPath = `tests/${currentTest}/img/${q.image}`;
                console.log(`Đường dẫn hình ảnh (Passage ${index + 1}, Câu ${q.id}):`, imgPath);
                qDiv.innerHTML += `<img src="${imgPath}" alt="Question ${q.id}" loading="lazy" onerror="this.src='tests/${currentTest}/img/placeholder.jpg';">`;
            }
            ['A', 'B', 'C', 'D'].forEach(opt => {
                if (q[`option${opt}`] && q[`option${opt}`].trim() !== '') {
                    qDiv.innerHTML += `
                        <div class="option">
                            <input type="radio" name="q${q.id}" value="${opt}" id="q${q.id}-${opt}">
                            <label for="q${q.id}-${opt}">${opt}</label>
                        </div>`;
                }
            });
            div.appendChild(qDiv);
        });
        questionSection.appendChild(div);
    }

    // Cập nhật danh sách câu hỏi
    function updateQuestionList(partQuestions) {
        questionList.innerHTML = '';
        partQuestions.forEach(q => {
            const btn = document.createElement('button');
            btn.textContent = q.id;
            btn.addEventListener('click', () => {
                const targetQuestion = document.querySelector(`.question input[name="q${q.id}"]`);
                if (targetQuestion) targetQuestion.scrollIntoView({ behavior: 'smooth' });
            });
            questionList.appendChild(btn);
        });
    }

    // Lưu và tải đáp án
    function saveAnswer(questionId, answer) {
        let answers = JSON.parse(localStorage.getItem(`toeicAnswers_${currentTest}`)) || {};
        answers[questionId] = answer;
        localStorage.setItem(`toeicAnswers_${currentTest}`, JSON.stringify(answers));
        updateQuestionStatus();
    }

    function loadAnswers() {
        const answers = JSON.parse(localStorage.getItem(`toeicAnswers_${currentTest}`)) || {};
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            const qId = input.name.replace('q', '');
            if (answers[qId] === input.value) input.checked = true;
            input.addEventListener('change', () => saveAnswer(qId, input.value));
        });
    }

    function updateQuestionStatus() {
        const answers = JSON.parse(localStorage.getItem(`toeicAnswers_${currentTest}`)) || {};
        document.querySelectorAll('.question-list button').forEach((btn, index) => {
            const qId = questions[currentPart][index]?.id;
            if (qId && answers[qId]) btn.classList.add('answered');
        });
    }

    // Chuyển đổi phần thi
    partButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            partButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentPart = btn.dataset.part;
            displayQuestions(currentPart);
        });
    });
    partButtons[0].classList.add('active');

    // Nộp bài và chấm điểm
    submitBtn.addEventListener('click', () => {
        if (confirm('Bạn có chắc muốn nộp bài?')) submitTest();
    });

    function submitTest() {
        clearInterval(timerInterval);
        const answers = JSON.parse(localStorage.getItem(`toeicAnswers_${currentTest}`)) || {};
        let score = 0;
        let totalQuestions = 0;
        for (let part in questions) {
            questions[part].forEach(q => {
                if (q.correct && q.correct.trim() !== '') {
                    totalQuestions++;
                    if (answers[q.id] === q.correct) score++;
                }
            });
        }
        scoreDisplay.textContent = `Điểm: ${score}/${totalQuestions}`;
        alert(`Bài thi ${currentTest.replace('test', 'Test ')} đã được nộp! Điểm của bạn: ${score}/${totalQuestions}`);
        localStorage.removeItem(`toeicAnswers_${currentTest}`);
        window.location.href = 'select_test.php';
    }

    // Thoát
    exitBtn.addEventListener('click', () => {
        if (confirm('Bạn có muốn thoát? Tiến trình sẽ không được lưu.')) {
            clearInterval(timerInterval);
            localStorage.removeItem(`toeicAnswers_${currentTest}`);
            window.location.href = 'select_test.php';
        }
    });

    // Nút cuộn
    window.addEventListener('scroll', () => {
        scrollTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
    });

    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Khởi động
    startTimer();
    loadQuestions();
});