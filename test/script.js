document.addEventListener('DOMContentLoaded', () => {
    const els = {
        questionSection: document.getElementById('questionSection'),
        questionList: document.getElementById('questionList'),
        timeDisplay: document.getElementById('timeDisplay'),
        submitBtn: document.getElementById('submitBtn'),
        exitBtn: document.getElementById('exitBtn'),
        scrollTopBtn: document.getElementById('scrollTopBtn'),
        partButtons: document.querySelectorAll('.part-btn'),
        scoreDisplay: document.getElementById('scoreDisplay'),
        audioPlayer: document.getElementById('audioPlayer'),
        audioSource: document.getElementById('audioSource'),
        testTitle: document.getElementById('testTitle')
    };

    let currentPart = 1, timeLeft = 120 * 60, timer, questions = {};
    const test = new URLSearchParams(window.location.search).get('test') || localStorage.getItem('currentTest') || 'test1';
    els.testTitle.textContent = test.replace('test', '');

    async function loadQuestions() {
        const data = await (await fetch(`get_questions.php?test=${test}`)).json();
        if (data.error) throw new Error(data.error);
        questions = data.reduce((acc, q) => {
            const part = parseInt(q.part);
            acc[part] = acc[part] || [];
            acc[part].push(q);
            return acc;
        }, {});
        displayQuestions(currentPart);
    }

    function startTimer() {
        timer = setInterval(() => {
            timeLeft--;
            els.timeDisplay.textContent = `${Math.floor(timeLeft / 60)}:${String(timeLeft % 60).padStart(2, '0')}`;
            if (timeLeft <= 0) { clearInterval(timer); alert('Hết giờ!'); submitTest(); }
        }, 1000);
    }

    async function updateAudio(part) {
        try {
            // Gửi yêu cầu đến API để lấy danh sách file âm thanh
            const response = await fetch(`get_audio_files.php?test=${test}`);
            const audioFiles = await response.json();

            if (audioFiles.error) {
                console.error(audioFiles.error);
                els.audioPlayer.style.display = 'none';
                els.audioSource.src = '';
                return;
            }

            // Hiển thị audio player
            els.audioPlayer.style.display = 'block';

            // Tạo container cho nhiều audio player
            const audioContainer = document.createElement('div');
            audioContainer.className = 'audio-playlist';
            audioFiles.forEach((src, index) => {
                const audio = document.createElement('audio');
                audio.controls = true;
                audio.src = src;
                audio.style.margin = '10px 0';
                audio.addEventListener('play', () => {
                    // Tạm dừng các audio khác khi một audio được phát
                    document.querySelectorAll('audio').forEach(a => {
                        if (a !== audio) a.pause();
                    });
                });
                // Thêm nhãn hiển thị tên file
                const fileName = src.split('/').pop();
                const label = document.createElement('p');
                label.textContent = `Âm thanh: ${fileName}`;
                label.style.margin = '5px 0';
                audioContainer.appendChild(label);
                audioContainer.appendChild(audio);
            });

            // Thay thế audio player hiện tại bằng container mới
            els.audioPlayer.parentNode.replaceChild(audioContainer, els.audioPlayer);
            els.audioPlayer = audioContainer;
        } catch (error) {
            console.error('Lỗi khi tải danh sách file âm thanh:', error);
            els.audioPlayer.style.display = 'none';
            els.audioSource.src = '';
        }
    }

    function displayQuestions(part) {
        els.questionSection.innerHTML = '';
        const qs = questions[part] || [];
        if (!qs.length) return els.questionSection.innerHTML = '<p>Không có câu hỏi.</p>';

        updateAudio(part);
        qs.forEach(q => {
            const div = document.createElement('div');
            div.classList.add('question');
            div.innerHTML = `<p>Câu ${q.id}: ${q.content || ''}</p>`;
            if (q.image) div.innerHTML += `<img src="../tests/${test}/img/${q.image}" alt="Câu ${q.id}">`;
            const opts = q.type === 'part2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
            opts.forEach(o => {
                if (q[`option${o}`]) div.innerHTML += `
                    <div class="option">
                        <input type="radio" name="q${q.id}" value="${o}" id="q${q.id}-${o}">
                        <label for="q${q.id}-${o}">${o}</label>
                    </div>`;
            });
            els.questionSection.appendChild(div);
        });
        updateQuestionList(qs);
        loadAnswers();
    }

    function updateQuestionList(qs) {
        els.questionList.innerHTML = qs.map(q => `<button onclick="document.querySelector('input[name=q${q.id}]')?.scrollIntoView()">${q.id}</button>`).join('');
    }

    function saveAnswer(qId, val) {
        const answers = JSON.parse(localStorage.getItem(`toeic_${test}`)) || {};
        answers[qId] = val;
        localStorage.setItem(`toeic_${test}`, JSON.stringify(answers));
    }

    function loadAnswers() {
        const answers = JSON.parse(localStorage.getItem(`toeic_${test}`)) || {};
        document.querySelectorAll('input[type="radio"]').forEach(i => {
            if (answers[i.name.replace('q', '')] === i.value) i.checked = true;
            i.addEventListener('change', () => saveAnswer(i.name.replace('q', ''), i.value));
        });
    }

    function submitTest() {
        clearInterval(timer);
        const answers = JSON.parse(localStorage.getItem(`toeic_${test}`)) || {};
        let score = 0, total = 0;
        Object.values(questions).flat().forEach(q => {
            if (q.correct) { total++; if (answers[q.id] === q.correct) score++; }
        });
        els.scoreDisplay.textContent = `Điểm: ${score}/${total}`;
        alert(`Điểm: ${score}/${total}`);
        localStorage.removeItem(`toeic_${test}`);
        window.location.href = 'select_test.php';
    }

    els.partButtons.forEach(b => b.addEventListener('click', () => {
        els.partButtons.forEach(btn => btn.classList.remove('active'));
        b.classList.add('active');
        currentPart = parseInt(b.dataset.part);
        displayQuestions(currentPart);
    }));
    els.submitBtn.addEventListener('click', () => confirm('Nộp bài?') && submitTest());
    els.exitBtn.addEventListener('click', () => confirm('Thoát?') && (clearInterval(timer), localStorage.removeItem(`toeic_${test}`), window.location.href = 'select_test.php'));
    els.scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    window.addEventListener('scroll', () => els.scrollTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none');

    startTimer();
    loadQuestions();
});