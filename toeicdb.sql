-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 05:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toeicdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied') DEFAULT 'pending',
  `reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `username`, `message`, `status`, `reply`, `created_at`, `replied_at`) VALUES
(6, 5, 'Nguyễn Ngọc Sinh', 'hhhhh', 'replied', 'sao dáy', '2025-04-11 22:41:42', '2025-04-11 22:42:00'),
(7, 5, 'Nguyễn Ngọc Sinh', 'admin oiii\r\n', 'replied', 'sao vay ak', '2025-04-13 21:43:23', '2025-04-13 21:44:09'),
(8, 24, 'Testweb1', 'admin ơiii', 'replied', 'sao v ạ', '2025-04-27 15:00:42', '2025-04-27 15:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content_file` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `teacher_name` varchar(255) NOT NULL,
  `video_file` varchar(255) NOT NULL,
  `category` enum('grammar','reading','listening','pronunciation','free') NOT NULL DEFAULT 'free',
  `media_type` enum('audio','video') DEFAULT 'video',
  `media_url` varchar(255) DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `description`, `image`, `content_file`, `start_date`, `end_date`, `created_at`, `title`, `price`, `teacher_name`, `video_file`, `category`, `media_type`, `media_url`, `learning_outcomes`, `likes`) VALUES
(1, 'Học Tiếng Anh Cùng Cô Mai Trang', 'Khoá học dành cho các bạn muốn lấy lại căn bản Tiếng anh và muốn học thêm những thứ nâng cao hơn !!', 'uploads/1.jpg', 'uploads/Solutions_Pre_Intermediate_Students_Book.pdf', NULL, NULL, '2025-04-11 20:22:00', 'Khoá học Tiếng anh cơ bản đến nâng cao', 0.00, 'Vũ Mai Trang', 'uploads/toeic550_intro.mp4', 'free', 'video', NULL, NULL, 0),
(12, 'Khóa học Tiếng Anh Matt', 'Khóa học Tiếng Anh Matt, dễ học dễ hiểu ai không hiểu thì thôi', '../Uploads/hq720.jpg', NULL, NULL, NULL, '2025-04-18 15:54:13', 'Khóa học Tiếng Anh Matt', 0.00, 'MattLotte', '', 'free', 'video', NULL, '', 0),
(13, 'Khóa TOEIC 750+ cô Mai Phương New Format 2025', 'Học cùng cô Mai Phương', '../Uploads/1744996179_60.jpg', NULL, NULL, NULL, '2025-04-18 17:09:39', 'Khóa TOEIC 750+ cô Mai Phương New Format 2025', 0.00, 'Mai Phương', '', 'free', 'video', NULL, '', 0),
(14, 'Test KH', 'Test KH', '../Uploads/1745766168_60.jpg', NULL, NULL, NULL, '2025-04-27 15:02:48', 'Test KH', 0.00, 'Test KH', '', 'free', 'video', NULL, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dictionary`
--

CREATE TABLE `dictionary` (
  `id` int(11) NOT NULL,
  `word` varchar(100) NOT NULL,
  `language` enum('en','vi') NOT NULL,
  `translation` varchar(255) NOT NULL,
  `word_type` varchar(50) DEFAULT NULL,
  `detailed_explanation` text DEFAULT NULL,
  `example` text DEFAULT NULL,
  `pronunciation` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dictionary`
--

INSERT INTO `dictionary` (`id`, `word`, `language`, `translation`, `word_type`, `detailed_explanation`, `example`, `pronunciation`, `created_at`) VALUES
(1, 'apple', 'en', 'táo', 'noun', 'A type of fruit that grows on trees.', 'I eat an apple every day.', '/ˈæp.l̩/', '2025-04-13 17:52:40'),
(2, 'táo', 'vi', 'apple', 'noun', 'Một loại trái cây mọc trên cây.', 'Tôi ăn một quả táo mỗi ngày.', NULL, '2025-04-13 17:52:40'),
(3, 'run', 'en', 'chạy', 'verb', 'To move quickly on foot.', 'She runs every morning.', '/rʌn/', '2025-04-13 17:52:40'),
(4, 'chạy', 'vi', 'run', 'verb', 'Di chuyển nhanh bằng chân.', 'Cô ấy chạy mỗi sáng.', NULL, '2025-04-13 17:52:40'),
(5, 'book', 'en', 'sách', 'noun', 'A written or printed work consisting of pages glued or sewn together along one side.', 'I read a book every week.', '/bʊk/', '2025-04-13 10:52:40'),
(6, 'sách', 'vi', 'book', 'noun', 'Một tác phẩm được viết hoặc in, gồm các trang được dán hoặc khâu lại ở một bên.', 'Tôi đọc một cuốn sách mỗi tuần.', NULL, '2025-04-13 10:52:40'),
(7, 'happy', 'en', 'vui vẻ', 'adjective', 'Feeling or showing pleasure or contentment.', 'She is happy with her new job.', '/ˈhæp.i/', '2025-04-13 10:52:40'),
(8, 'vui vẻ', 'vi', 'happy', 'adjective', 'Cảm thấy hoặc thể hiện niềm vui hoặc sự hài lòng.', 'Cô ấy vui vẻ với công việc mới.', NULL, '2025-04-13 10:52:40'),
(9, 'water', 'en', 'nước', 'noun', 'A colorless, transparent, odorless liquid that forms the seas, lakes, rivers, and rain.', 'Drink plenty of water every day.', '/ˈwɔː.tər/', '2025-04-13 10:52:40'),
(10, 'nước', 'vi', 'water', 'noun', 'Một chất lỏng không màu, trong suốt, không mùi, tạo thành biển, hồ, sông và mưa.', 'Uống nhiều nước mỗi ngày.', NULL, '2025-04-13 10:52:40'),
(11, 'eat', 'en', 'ăn', 'verb', 'To put food into the mouth and chew and swallow it.', 'We eat dinner at 7 PM.', '/iːt/', '2025-04-13 10:52:40'),
(12, 'ăn', 'vi', 'eat', 'verb', 'Cho thức ăn vào miệng, nhai và nuốt.', 'Chúng tôi ăn tối lúc 7 giờ.', NULL, '2025-04-13 10:52:40'),
(13, 'beautiful', 'en', 'đẹp', 'adjective', 'Pleasing the senses or mind aesthetically.', 'The sunset is beautiful.', '/ˈbjuː.tɪ.fəl/', '2025-04-13 10:52:40'),
(14, 'đẹp', 'vi', 'beautiful', 'adjective', 'Làm hài lòng các giác quan hoặc tâm trí về mặt thẩm mỹ.', 'Hoàng hôn rất đẹp.', NULL, '2025-04-13 10:52:40'),
(15, 'dog', 'en', 'chó', 'noun', 'A domesticated carnivorous mammal.', 'My dog loves to play fetch.', '/dɒɡ/', '2025-04-13 10:52:40'),
(16, 'chó', 'vi', 'dog', 'noun', 'Một loài động vật có vú ăn thịt được thuần hóa.', 'Con chó của tôi thích chơi đuổi bắt.', NULL, '2025-04-13 10:52:40'),
(17, 'sleep', 'en', 'ngủ', 'verb', 'To rest by being in a state of unconsciousness.', 'I sleep for eight hours every night.', '/sliːp/', '2025-04-13 10:52:40'),
(18, 'ngủ', 'vi', 'sleep', 'verb', 'Nghỉ ngơi bằng cách ở trong trạng thái không ý thức.', 'Tôi ngủ tám tiếng mỗi đêm.', NULL, '2025-04-13 10:52:40'),
(19, 'sky', 'en', 'bầu trời', 'noun', 'The region above the earth where clouds, the sun, and stars appear.', 'The sky is clear tonight.', '/skaɪ/', '2025-04-13 10:52:40'),
(20, 'bầu trời', 'vi', 'sky', 'noun', 'Vùng phía trên mặt đất nơi có mây, mặt trời và các ngôi sao xuất hiện.', 'Bầu trời đêm nay trong vắt.', NULL, '2025-04-13 10:52:40'),
(21, 'computer', 'en', 'máy tính', 'noun', 'An electronic device for storing and processing data.', 'I use my computer for work.', '/kəmˈpjuː.tər/', '2025-04-13 10:52:40'),
(22, 'máy tính', 'vi', 'computer', 'noun', 'Một thiết bị điện tử dùng để lưu trữ và xử lý dữ liệu.', 'Tôi dùng máy tính để làm việc.', NULL, '2025-04-13 10:52:40'),
(23, 'fast', 'en', 'nhanh', 'adjective', 'Moving or capable of moving at high speed.', 'The car is very fast.', '/fæst/', '2025-04-13 10:52:40'),
(24, 'nhanh', 'vi', 'fast', 'adjective', 'Di chuyển hoặc có khả năng di chuyển với tốc độ cao.', 'Chiếc xe rất nhanh.', NULL, '2025-04-13 10:52:40'),
(25, 'love', 'en', 'yêu', 'verb', 'To feel deep affection for someone or something.', 'I love my family.', '/lʌv/', '2025-04-13 10:52:40'),
(26, 'yêu', 'vi', 'love', 'verb', 'Cảm thấy tình cảm sâu sắc đối với ai đó hoặc điều gì đó.', 'Tôi yêu gia đình tôi.', NULL, '2025-04-13 10:52:40'),
(27, 'house', 'en', 'nhà', 'noun', 'A building for human habitation.', 'We live in a small house.', '/haʊs/', '2025-04-13 10:52:40'),
(28, 'nhà', 'vi', 'house', 'noun', 'Một tòa nhà để con người sinh sống.', 'Chúng tôi sống trong một ngôi nhà nhỏ.', NULL, '2025-04-13 10:52:40'),
(29, 'study', 'en', 'học', 'verb', 'To acquire knowledge through education or practice.', 'She studies biology at university.', '/ˈstʌd.i/', '2025-04-13 10:52:40'),
(30, 'học', 'vi', 'study', 'verb', 'Thu nhận kiến thức thông qua giáo dục hoặc thực hành.', 'Cô ấy học sinh học ở đại học.', NULL, '2025-04-13 10:52:40'),
(31, 'tree', 'en', 'cây', 'noun', 'A tall plant with a trunk and branches made of wood.', 'The tree in our yard is very old.', '/triː/', '2025-04-13 10:52:40'),
(32, 'cây', 'vi', 'tree', 'noun', 'Một loại thực vật cao với thân và cành làm từ gỗ.', 'Cây trong sân nhà chúng tôi rất cũ.', NULL, '2025-04-13 10:52:40'),
(33, 'music', 'en', 'âm nhạc', 'noun', 'Vocal or instrumental sounds combined to produce harmony and expression.', 'I listen to music every day.', '/ˈmjuː.zɪk/', '2025-04-13 10:52:40'),
(34, 'âm nhạc', 'vi', 'music', 'noun', 'Âm thanh giọng hát hoặc nhạc cụ được kết hợp để tạo ra sự hài hòa và biểu cảm.', 'Tôi nghe nhạc mỗi ngày.', NULL, '2025-04-13 10:52:40'),
(35, 'walk', 'en', 'đi bộ', 'verb', 'To move at a regular pace by lifting and setting down each foot in turn.', 'We walk to school every day.', '/wɔːk/', '2025-04-13 10:52:40'),
(36, 'đi bộ', 'vi', 'walk', 'verb', 'Di chuyển với tốc độ đều bằng cách nhấc và đặt từng chân xuống lần lượt.', 'Chúng tôi đi bộ đến trường mỗi ngày.', NULL, '2025-04-13 10:52:40'),
(37, 'sun', 'en', 'mặt trời', 'noun', 'The star at the center of the solar system.', 'The sun rises every morning.', '/sʌn/', '2025-04-13 10:52:40'),
(38, 'mặt trời', 'vi', 'sun', 'noun', 'Ngôi sao ở trung tâm của hệ mặt trời.', 'Mặt trời mọc mỗi buổi sáng.', NULL, '2025-04-13 10:52:40'),
(39, 'big', 'en', 'lớn', 'adjective', 'Of considerable size or extent.', 'This is a big house.', '/bɪɡ/', '2025-04-13 10:52:40'),
(40, 'lớn', 'vi', 'big', 'adjective', 'Có kích thước hoặc phạm vi đáng kể.', 'Đây là một ngôi nhà lớn.', NULL, '2025-04-13 10:52:40'),
(41, 'friend', 'en', 'bạn', 'noun', 'A person with whom one has a bond of mutual affection.', 'My friend is coming over today.', '/frend/', '2025-04-13 10:52:40'),
(42, 'bạn', 'vi', 'friend', 'noun', 'Người mà một người có mối quan hệ thân thiết và tình cảm lẫn nhau.', 'Bạn tôi sẽ đến chơi hôm nay.', NULL, '2025-04-13 10:52:40'),
(43, 'work', 'en', 'làm việc', 'verb', 'To perform a task or job, typically for payment.', 'I work at a hospital.', '/wɜːrk/', '2025-04-13 10:52:40'),
(44, 'làm việc', 'vi', 'work', 'verb', 'Thực hiện một nhiệm vụ hoặc công việc, thường để được trả tiền.', 'Tôi làm việc ở bệnh viện.', NULL, '2025-04-13 10:52:40'),
(45, 'flower', 'en', 'hoa', 'noun', 'The reproductive structure of a plant, often colorful and fragrant.', 'She gave me a flower.', '/ˈflaʊ.ər/', '2025-04-13 10:52:40'),
(46, 'hoa', 'vi', 'flower', 'noun', 'Cấu trúc sinh sản của thực vật, thường có màu sắc và mùi hương.', 'Cô ấy tặng tôi một bông hoa.', NULL, '2025-04-13 10:52:40'),
(47, 'quickly', 'en', 'nhanh chóng', 'adverb', 'At a fast speed; rapidly.', 'He finished his homework quickly.', '/ˈkwɪk.li/', '2025-04-13 10:52:40'),
(48, 'nhanh chóng', 'vi', 'quickly', 'adverb', 'Với tốc độ nhanh; một cách mau lẹ.', 'Anh ấy hoàn thành bài tập nhanh chóng.', NULL, '2025-04-13 10:52:40'),
(49, 'car', 'en', 'xe hơi', 'noun', 'A wheeled motor vehicle used for transportation.', 'I drive a car to work.', '/kɑːr/', '2025-04-13 10:52:40'),
(50, 'xe hơi', 'vi', 'car', 'noun', 'Một phương tiện có động cơ và bánh xe dùng để vận chuyển.', 'Tôi lái xe hơi đi làm.', NULL, '2025-04-13 10:52:40'),
(51, 'smile', 'en', 'cười', 'verb', 'To form one\'s features into a pleased or kind expression.', 'She smiles at everyone.', '/smaɪl/', '2025-04-13 10:52:40'),
(52, 'cười', 'vi', 'smile', 'verb', 'Tạo hình nét mặt thành biểu cảm vui vẻ hoặc thân thiện.', 'Cô ấy cười với mọi người.', NULL, '2025-04-13 10:52:40'),
(53, 'cloud', 'en', 'mây', 'noun', 'A visible mass of condensed water vapor in the atmosphere.', 'The sky is full of clouds.', '/klaʊd/', '2025-04-13 10:52:40'),
(54, 'mây', 'vi', 'cloud', 'noun', 'Một khối hơi nước ngưng tụ có thể nhìn thấy trong khí quyển.', 'Bầu trời đầy mây.', NULL, '2025-04-13 10:52:40'),
(55, 'small', 'en', 'nhỏ', 'adjective', 'Of a size that is less than normal or usual.', 'This is a small room.', '/smɔːl/', '2025-04-13 10:52:40'),
(56, 'nhỏ', 'vi', 'small', 'adjective', 'Có kích thước nhỏ hơn bình thường hoặc thông thường.', 'Đây là một căn phòng nhỏ.', NULL, '2025-04-13 10:52:40'),
(57, 'play', 'en', 'chơi', 'verb', 'To engage in activity for enjoyment and recreation.', 'The children play in the park.', '/pleɪ/', '2025-04-13 10:52:40'),
(58, 'chơi', 'vi', 'play', 'verb', 'Tham gia vào hoạt động để giải trí và vui vẻ.', 'Bọn trẻ chơi ở công viên.', NULL, '2025-04-13 10:52:40'),
(59, 'food', 'en', 'thức ăn', 'noun', 'Any nutritious substance that people or animals eat or drink.', 'I love Italian food.', '/fuːd/', '2025-04-13 10:52:40'),
(60, 'thức ăn', 'vi', 'food', 'noun', 'Bất kỳ chất dinh dưỡng nào mà con người hoặc động vật ăn hoặc uống.', 'Tôi thích thức ăn Ý.', NULL, '2025-04-13 10:52:40'),
(62, 'mèo', 'vi', 'cat', 'noun', 'Một loài động vật có vú ăn thịt nhỏ được thuần hóa với bộ lông mềm.', 'Con mèo đang ngủ trên ghế sofa.', NULL, '2025-04-13 10:52:40'),
(63, 'write', 'en', 'viết', 'verb', 'To mark letters, words, or symbols on a surface, typically paper.', 'She writes a letter to her friend.', '/raɪt/', '2025-04-13 10:52:40'),
(64, 'viết', 'vi', 'write', 'verb', 'Đánh dấu các chữ cái, từ hoặc ký hiệu trên một bề mặt, thường là giấy.', 'Cô ấy viết thư cho bạn mình.', NULL, '2025-04-13 10:52:40'),
(65, 'blue', 'en', 'xanh lam', 'adjective', 'Of a color intermediate between green and violet.', 'The sea is blue today.', '/bluː/', '2025-04-13 10:52:40'),
(66, 'xanh lam', 'vi', 'blue', 'adjective', 'Có màu trung gian giữa xanh lá cây và tím.', 'Biển hôm nay màu xanh lam.', NULL, '2025-04-13 10:52:40'),
(67, 'bird', 'en', 'chim', 'noun', 'A warm-blooded egg-laying vertebrate with feathers and wings.', 'A bird is singing in the tree.', '/bɜːrd/', '2025-04-13 10:52:40'),
(68, 'chim', 'vi', 'bird', 'noun', 'Một loài động vật có xương sống đẻ trứng, có lông vũ và cánh.', 'Một con chim đang hót trên cây.', NULL, '2025-04-13 10:52:40'),
(69, 'read', 'en', 'đọc', 'verb', 'To look at and comprehend the meaning of written matter.', 'He reads a book every night.', '/riːd/', '2025-04-13 10:52:40'),
(70, 'đọc', 'vi', 'read', 'verb', 'Nhìn và hiểu ý nghĩa của văn bản viết.', 'Anh ấy đọc sách mỗi tối.', NULL, '2025-04-13 10:52:40'),
(71, 'hot', 'en', 'nóng', 'adjective', 'Having a high degree of heat or temperature.', 'The coffee is too hot to drink.', '/hɒt/', '2025-04-13 10:52:40'),
(72, 'nóng', 'vi', 'hot', 'adjective', 'Có mức độ nhiệt hoặc nhiệt độ cao.', 'Cà phê quá nóng để uống.', NULL, '2025-04-13 10:52:40'),
(73, 'school', 'en', 'trường học', 'noun', 'An institution for educating children or students.', 'She goes to school every day.', '/skuːl/', '2025-04-13 10:52:40'),
(74, 'trường học', 'vi', 'school', 'noun', 'Một cơ sở để giáo dục trẻ em hoặc học sinh.', 'Cô ấy đi học mỗi ngày.', NULL, '2025-04-13 10:52:40'),
(75, 'sing', 'en', 'hát', 'verb', 'To produce musical sounds with the voice.', 'They sing a song together.', '/sɪŋ/', '2025-04-13 10:52:40'),
(76, 'hát', 'vi', 'sing', 'verb', 'Tạo ra âm thanh nhạc bằng giọng nói.', 'Họ cùng hát một bài hát.', NULL, '2025-04-13 10:52:40'),
(77, 'red', 'en', 'đỏ', 'adjective', 'Of a color at the end of the spectrum next to orange.', 'Her dress is red.', '/red/', '2025-04-13 10:52:40'),
(78, 'đỏ', 'vi', 'red', 'adjective', 'Có màu ở cuối quang phổ bên cạnh màu cam.', 'Chiếc váy của cô ấy màu đỏ.', NULL, '2025-04-13 10:52:40'),
(79, 'table', 'en', 'bàn', 'noun', 'A piece of furniture with a flat top and legs.', 'The book is on the table.', '/ˈteɪ.bəl/', '2025-04-13 10:52:40'),
(80, 'bàn', 'vi', 'table', 'noun', 'Một món đồ nội thất có mặt phẳng và chân.', 'Cuốn sách ở trên bàn.', NULL, '2025-04-13 10:52:40'),
(81, 'dance', 'en', 'nhảy múa', 'verb', 'To move rhythmically to music.', 'They dance at the party.', '/dæns/', '2025-04-13 10:52:40'),
(82, 'nhảy múa', 'vi', 'dance', 'verb', 'Di chuyển theo nhịp điệu với âm nhạc.', 'Họ nhảy múa ở bữa tiệc.', NULL, '2025-04-13 10:52:40'),
(83, 'cold', 'en', 'lạnh', 'adjective', 'Having a low temperature.', 'The water is cold.', '/kəʊld/', '2025-04-13 10:52:40'),
(84, 'lạnh', 'vi', 'cold', 'adjective', 'Có nhiệt độ thấp.', 'Nước lạnh.', NULL, '2025-04-13 10:52:40'),
(85, 'chair', 'en', 'ghế', 'noun', 'A seat for one person, typically with a back and four legs.', 'Sit on the chair.', '/tʃeər/', '2025-04-13 10:52:40'),
(86, 'ghế', 'vi', 'chair', 'noun', 'Một chỗ ngồi cho một người, thường có lưng tựa và bốn chân.', 'Ngồi lên ghế.', NULL, '2025-04-13 10:52:40'),
(87, 'listen', 'en', 'nghe', 'verb', 'To give attention to a sound or spoken words.', 'I listen to the radio.', '/ˈlɪs.ən/', '2025-04-13 10:52:40'),
(88, 'nghe', 'vi', 'listen', 'verb', 'Chú ý đến âm thanh hoặc lời nói.', 'Tôi nghe radio.', NULL, '2025-04-13 10:52:40'),
(89, 'green', 'en', 'xanh lục', 'adjective', 'Of a color between blue and yellow.', 'The grass is green.', '/ɡriːn/', '2025-04-13 10:52:40'),
(90, 'xanh lục', 'vi', 'green', 'adjective', 'Có màu trung gian giữa xanh lam và vàng.', 'Cỏ màu xanh lục.', NULL, '2025-04-13 10:52:40'),
(91, 'door', 'en', 'cửa', 'noun', 'A movable barrier used to close off an entrance.', 'Open the door, please.', '/dɔːr/', '2025-04-13 10:52:40'),
(92, 'cửa', 'vi', 'door', 'noun', 'Một rào chắn có thể di chuyển để đóng lối vào.', 'Mở cửa ra, làm ơn.', NULL, '2025-04-13 10:52:40'),
(93, 'laugh', 'en', 'cười lớn', 'verb', 'To make sounds expressing amusement or joy.', 'We laugh at the joke.', '/læf/', '2025-04-13 10:52:40'),
(94, 'cười lớn', 'vi', 'laugh', 'verb', 'Phát ra âm thanh thể hiện sự vui vẻ hoặc thích thú.', 'Chúng tôi cười vì câu đùa.', NULL, '2025-04-13 10:52:40'),
(95, 'yellow', 'en', 'vàng', 'adjective', 'Of a color between green and orange.', 'The sunflowers are yellow.', '/ˈjel.əʊ/', '2025-04-13 10:52:40'),
(96, 'vàng', 'vi', 'yellow', 'adjective', 'Có màu trung gian giữa xanh lục và cam.', 'Hoa hướng dương màu vàng.', NULL, '2025-04-13 10:52:40'),
(97, 'window', 'en', 'cửa sổ', 'noun', 'An opening in a wall for light and air, usually with glass.', 'Look out the window.', '/ˈwɪn.dəʊ/', '2025-04-13 10:52:40'),
(98, 'cửa sổ', 'vi', 'window', 'noun', 'Một lỗ mở trên tường để lấy ánh sáng và không khí, thường có kính.', 'Nhìn ra cửa sổ.', NULL, '2025-04-13 10:52:40'),
(99, 'talk', 'en', 'nói chuyện', 'verb', 'To express thoughts or feelings in spoken words.', 'We talk about our day.', '/tɔːk/', '2025-04-13 10:52:40'),
(100, 'nói chuyện', 'vi', 'talk', 'verb', 'Bày tỏ suy nghĩ hoặc cảm xúc bằng lời nói.', 'Chúng tôi nói về ngày hôm nay.', NULL, '2025-04-13 10:52:40'),
(101, 'white', 'en', 'trắng', 'adjective', 'Of the color of snow or milk.', 'Her shirt is white.', '/waɪt/', '2025-04-13 10:52:40'),
(102, 'trắng', 'vi', 'white', 'adjective', 'Có màu của tuyết hoặc sữa.', 'Áo của cô ấy màu trắng.', NULL, '2025-04-13 10:52:40'),
(103, 'bed', 'en', 'giường', 'noun', 'A piece of furniture for sleep or rest.', 'I go to bed at 10 PM.', '/bed/', '2025-04-13 10:52:40'),
(104, 'giường', 'vi', 'bed', 'noun', 'Một món đồ nội thất để ngủ hoặc nghỉ ngơi.', 'Tôi đi ngủ lúc 10 giờ tối.', NULL, '2025-04-13 10:52:40'),
(105, 'jump', 'en', 'nhảy', 'verb', 'To push oneself off a surface into the air.', 'The kids jump on the bed.', '/dʒʌmp/', '2025-04-13 10:52:40'),
(106, 'nhảy', 'vi', 'jump', 'verb', 'Đẩy mình ra khỏi một bề mặt lên không trung.', 'Bọn trẻ nhảy trên giường.', NULL, '2025-04-13 10:52:40'),
(107, 'black', 'en', 'đen', 'adjective', 'Of the color absorbing all light.', 'The night sky is black.', '/blæk/', '2025-04-13 10:52:40'),
(108, 'đen', 'vi', 'black', 'adjective', 'Có màu hấp thụ tất cả ánh sáng.', 'Bầu trời đêm màu đen.', NULL, '2025-04-13 10:52:40'),
(109, 'kitchen', 'en', 'nhà bếp', 'noun', 'A room or area where food is prepared and cooked.', 'She cooks in the kitchen.', '/ˈkɪtʃ.ən/', '2025-04-13 10:52:40'),
(110, 'nhà bếp', 'vi', 'kitchen', 'noun', 'Một căn phòng hoặc khu vực nơi thức ăn được chuẩn bị và nấu nướng.', 'Cô ấy nấu ăn trong nhà bếp.', NULL, '2025-04-13 10:52:40'),
(111, 'swim', 'en', 'bơi', 'verb', 'To move through water by moving the body.', 'They swim in the pool.', '/swɪm/', '2025-04-13 10:52:40'),
(112, 'bơi', 'vi', 'swim', 'verb', 'Di chuyển qua nước bằng cách cử động cơ thể.', 'Họ bơi ở hồ bơi.', NULL, '2025-04-13 10:52:40'),
(113, 'pink', 'en', 'hồng', 'adjective', 'Of a color between red and white.', 'Her bag is pink.', '/pɪŋk/', '2025-04-13 10:52:40'),
(114, 'hồng', 'vi', 'pink', 'adjective', 'Có màu trung gian giữa đỏ và trắng.', 'Túi của cô ấy màu hồng.', NULL, '2025-04-13 10:52:40'),
(115, 'garden', 'en', 'vườn', 'noun', 'A piece of land used for growing plants or flowers.', 'We plant roses in the garden.', '/ˈɡɑːr.dən/', '2025-04-13 10:52:40'),
(116, 'vườn', 'vi', 'garden', 'noun', 'Một mảnh đất dùng để trồng cây hoặc hoa.', 'Chúng tôi trồng hoa hồng trong vườn.', NULL, '2025-04-13 10:52:40'),
(117, 'draw', 'en', 'vẽ', 'verb', 'To produce a picture or diagram by making lines.', 'He draws a tree.', '/drɔː/', '2025-04-13 10:52:40'),
(118, 'vẽ', 'vi', 'draw', 'verb', 'Tạo ra một bức tranh hoặc sơ đồ bằng cách vẽ đường nét.', 'Anh ấy vẽ một cái cây.', NULL, '2025-04-13 10:52:40'),
(119, 'brown', 'en', 'nâu', 'adjective', 'Of a color produced by mixing red, yellow, and black.', 'His shoes are brown.', '/braʊn/', '2025-04-13 10:52:40'),
(120, 'nâu', 'vi', 'brown', 'adjective', 'Có màu được tạo ra bằng cách trộn đỏ, vàng và đen.', 'Đôi giày của anh ấy màu nâu.', NULL, '2025-04-13 10:52:40'),
(121, 'road', 'en', 'đường', 'noun', 'A wide way leading from one place to another.', 'The road is busy today.', '/rəʊd/', '2025-04-13 10:52:40'),
(122, 'đường', 'vi', 'road', 'noun', 'Một con đường rộng dẫn từ nơi này đến nơi khác.', 'Con đường hôm nay rất đông.', NULL, '2025-04-13 10:52:40'),
(123, 'cry', 'en', 'khóc', 'verb', 'To shed tears as an expression of emotion.', 'The baby cries at night.', '/kraɪ/', '2025-04-13 10:52:40'),
(124, 'khóc', 'vi', 'cry', 'verb', 'Rơi nước mắt như một biểu hiện của cảm xúc.', 'Em bé khóc vào ban đêm.', NULL, '2025-04-13 10:52:40'),
(125, 'purple', 'en', 'tím', 'adjective', 'Of a color between red and blue.', 'The grapes are purple.', '/ˈpɜːr.pəl/', '2025-04-13 10:52:40'),
(126, 'tím', 'vi', 'purple', 'adjective', 'Có màu trung gian giữa đỏ và xanh lam.', 'Quả nho màu tím.', NULL, '2025-04-13 10:52:40'),
(127, 'park', 'en', 'công viên', 'noun', 'A public area of land for recreation.', 'We walk in the park.', '/pɑːrk/', '2025-04-13 10:52:40'),
(128, 'công viên', 'vi', 'park', 'noun', 'Một khu vực đất công cộng để giải trí.', 'Chúng tôi đi bộ trong công viên.', NULL, '2025-04-13 10:52:40'),
(129, 'buy', 'en', 'mua', 'verb', 'To acquire by payment.', 'I buy bread every day.', '/baɪ/', '2025-04-13 10:52:40'),
(130, 'mua', 'vi', 'buy', 'verb', 'Sở hữu bằng cách trả tiền.', 'Tôi mua bánh mì mỗi ngày.', NULL, '2025-04-13 10:52:40'),
(131, 'gray', 'en', 'xám', 'adjective', 'Of a color between black and white.', 'The sky is gray today.', '/ɡreɪ/', '2025-04-13 10:52:40'),
(132, 'xám', 'vi', 'gray', 'adjective', 'Có màu trung gian giữa đen và trắng.', 'Bầu trời hôm nay màu xám.', NULL, '2025-04-13 10:52:40'),
(133, 'shop', 'en', 'cửa hàng', 'noun', 'A place where goods are sold.', 'She goes to the shop.', '/ʃɒp/', '2025-04-13 10:52:40'),
(134, 'cửa hàng', 'vi', 'shop', 'noun', 'Một nơi bán hàng hóa.', 'Cô ấy đi đến cửa hàng.', NULL, '2025-04-13 10:52:40'),
(135, 'watch', 'en', 'xem', 'verb', 'To observe or look at something.', 'We watch a movie.', '/wɒtʃ/', '2025-04-13 10:52:40'),
(136, 'xem', 'vi', 'watch', 'verb', 'Quan sát hoặc nhìn vào một thứ gì đó.', 'Chúng tôi xem phim.', NULL, '2025-04-13 10:52:40'),
(137, 'orange', 'en', 'cam', 'adjective', 'Of a color between red and yellow.', 'Her scarf is orange.', '/ˈɒr.ɪndʒ/', '2025-04-13 10:52:40'),
(138, 'cam', 'vi', 'orange', 'adjective', 'Có màu trung gian giữa đỏ và vàng.', 'Khăn choàng của cô ấy màu cam.', NULL, '2025-04-13 10:52:40'),
(139, 'street', 'en', 'phố', 'noun', 'A public thoroughfare in a built-up area.', 'The street is quiet.', '/striːt/', '2025-04-13 10:52:40'),
(140, 'phố', 'vi', 'street', 'noun', 'Một con đường công cộng trong khu vực xây dựng.', 'Con phố rất yên tĩnh.', NULL, '2025-04-13 10:52:40'),
(141, 'schedule', 'en', 'lịch trình', 'noun', 'A plan for carrying out a process or procedure, giving lists of intended events and times.', 'The manager shared the project schedule with the team.', '/ˈskɛdʒ.uːl/', '2025-04-15 20:13:44'),
(142, 'lịch trình', 'vi', 'schedule', 'noun', 'Một kế hoạch để thực hiện một quá trình hoặc quy trình, cung cấp danh sách các sự kiện và thời gian dự kiến.', 'Người quản lý đã chia sẻ lịch trình dự án với đội nhóm.', NULL, '2025-04-15 20:13:44'),
(143, 'meeting', 'en', 'cuộc họp', 'noun', 'An assembly of people for a particular purpose, especially for formal discussion.', 'We have a staff meeting every Monday.', '/ˈmiː.tɪŋ/', '2025-04-15 20:13:44'),
(144, 'cuộc họp', 'vi', 'meeting', 'noun', 'Một cuộc tụ họp của mọi người vì một mục đích cụ thể, đặc biệt là để thảo luận chính thức.', 'Chúng tôi có cuộc họp nhân viên mỗi thứ Hai.', NULL, '2025-04-15 20:13:44'),
(145, 'invoice', 'en', 'hóa đơn', 'noun', 'A document listing goods or services provided and their prices, sent to a buyer.', 'The supplier sent an invoice for the equipment.', '/ˈɪn.vɔɪs/', '2025-04-15 20:13:44'),
(146, 'hóa đơn', 'vi', 'invoice', 'noun', 'Một tài liệu liệt kê hàng hóa hoặc dịch vụ được cung cấp và giá của chúng, được gửi đến người mua.', 'Nhà cung cấp đã gửi hóa đơn cho thiết bị.', NULL, '2025-04-15 20:13:44'),
(147, 'reservation', 'en', 'đặt chỗ', 'noun', 'An arrangement to secure a service or product, such as a seat or room, in advance.', 'I made a reservation for two at the restaurant.', '/ˌrɛz.ərˈveɪ.ʃən/', '2025-04-15 20:13:44'),
(148, 'đặt chỗ', 'vi', 'reservation', 'noun', 'Sự sắp xếp để đảm bảo một dịch vụ hoặc sản phẩm, chẳng hạn như chỗ ngồi hoặc phòng, trước thời hạn.', 'Tôi đã đặt chỗ cho hai người tại nhà hàng.', NULL, '2025-04-15 20:13:44'),
(149, 'promotion', 'en', 'thăng chức', 'noun', 'The act of raising someone to a higher position or rank within an organization.', 'She received a promotion after two years of hard work.', '/prəˈmoʊ.ʃən/', '2025-04-15 20:13:44'),
(150, 'thăng chức', 'vi', 'promotion', 'noun', 'Hành động nâng ai đó lên một vị trí hoặc cấp bậc cao hơn trong một tổ chức.', 'Cô ấy được thăng chức sau hai năm làm việc chăm chỉ.', NULL, '2025-04-15 20:13:44'),
(151, 'deadline', 'en', 'hạn chót', 'noun', 'The latest time or date by which something should be completed.', 'The project deadline is next Friday.', '/ˈded.laɪn/', '2025-04-15 20:14:56'),
(152, 'hạn chót', 'vi', 'deadline', 'noun', 'Thời gian hoặc ngày muộn nhất mà một việc gì đó phải được hoàn thành.', 'Hạn chót của dự án là thứ Sáu tới.', NULL, '2025-04-15 20:14:56'),
(153, 'conference', 'en', 'hội nghị', 'noun', 'A formal meeting for discussion, often involving multiple participants.', 'She attended an international conference last week.', '/ˈkɒn.fər.əns/', '2025-04-15 20:14:56'),
(154, 'hội nghị', 'vi', 'conference', 'noun', 'Một cuộc họp chính thức để thảo luận, thường có nhiều người tham gia.', 'Cô ấy đã tham dự một hội nghị quốc tế tuần trước.', NULL, '2025-04-15 20:14:56'),
(155, 'budget', 'en', 'ngân sách', 'noun', 'An estimate of income and expenditure for a set period of time.', 'The company approved the annual budget.', '/ˈbʌdʒ.ɪt/', '2025-04-15 20:14:56'),
(156, 'ngân sách', 'vi', 'budget', 'noun', 'Một ước tính về thu nhập và chi tiêu cho một khoảng thời gian nhất định.', 'Công ty đã phê duyệt ngân sách hàng năm.', NULL, '2025-04-15 20:14:56'),
(157, 'contract', 'en', 'hợp đồng', 'noun', 'A written or spoken agreement that is enforceable by law.', 'He signed a contract with the new supplier.', '/ˈkɒn.trækt/', '2025-04-15 20:14:56'),
(158, 'hợp đồng', 'vi', 'contract', 'noun', 'Một thỏa thuận bằng văn bản hoặc lời nói có thể được thực thi bởi luật pháp.', 'Anh ấy đã ký hợp đồng với nhà cung cấp mới.', NULL, '2025-04-15 20:14:56'),
(159, 'employee', 'en', 'nhân viên', 'noun', 'A person who works for an organization in exchange for compensation.', 'The company hired ten new employees this month.', '/ɪmˈplɔɪ.iː/', '2025-04-15 20:14:56'),
(160, 'nhân viên', 'vi', 'employee', 'noun', 'Một người làm việc cho một tổ chức để đổi lấy thù lao.', 'Công ty đã thuê mười nhân viên mới trong tháng này.', NULL, '2025-04-15 20:14:56'),
(161, 'office', 'en', 'văn phòng', 'noun', 'A room or building where people work, especially at administrative or professional tasks.', 'She works in the main office downtown.', '/ˈɒf.ɪs/', '2025-04-15 20:16:55'),
(162, 'văn phòng', 'vi', 'office', 'noun', 'Một căn phòng hoặc tòa nhà nơi mọi người làm việc, đặc biệt là các công việc hành chính hoặc chuyên môn.', 'Cô ấy làm việc ở văn phòng chính tại trung tâm.', NULL, '2025-04-15 20:16:55'),
(163, 'manager', 'en', 'quản lý', 'noun', 'A person responsible for controlling or administering an organization or group.', 'The manager approved the new project.', '/ˈmæn.ɪ.dʒər/', '2025-04-15 20:16:55'),
(164, 'quản lý', 'vi', 'manager', 'noun', 'Người chịu trách nhiệm kiểm soát hoặc quản lý một tổ chức hoặc nhóm.', 'Người quản lý đã phê duyệt dự án mới.', NULL, '2025-04-15 20:16:55'),
(165, 'customer', 'en', 'khách hàng', 'noun', 'A person who buys goods or services from a business.', 'The customer asked for a refund.', '/ˈkʌs.tə.mər/', '2025-04-15 20:16:55'),
(166, 'khách hàng', 'vi', 'customer', 'noun', 'Người mua hàng hóa hoặc dịch vụ từ một doanh nghiệp.', 'Khách hàng yêu cầu hoàn tiền.', NULL, '2025-04-15 20:16:55'),
(167, 'flight', 'en', 'chuyến bay', 'noun', 'A journey made by an aircraft, especially a commercial airplane.', 'My flight to Tokyo leaves at 8 PM.', '/flaɪt/', '2025-04-15 20:16:55'),
(168, 'chuyến bay', 'vi', 'flight', 'noun', 'Hành trình được thực hiện bằng máy bay, đặc biệt là máy bay thương mại.', 'Chuyến bay của tôi đến Tokyo khởi hành lúc 8 giờ tối.', NULL, '2025-04-15 20:16:55'),
(169, 'hotel', 'en', 'khách sạn', 'noun', 'An establishment providing accommodation and meals for travelers.', 'We booked a room at the hotel.', '/həʊˈtel/', '2025-04-15 20:16:55'),
(170, 'khách sạn', 'vi', 'hotel', 'noun', 'Một cơ sở cung cấp chỗ ở và bữa ăn cho khách du lịch.', 'Chúng tôi đã đặt phòng tại khách sạn.', NULL, '2025-04-15 20:16:55'),
(171, 'report', 'en', 'báo cáo', 'noun', 'A detailed account of an event, situation, or activity, often written or spoken.', 'She submitted a sales report yesterday.', '/rɪˈpɔːrt/', '2025-04-15 20:16:55'),
(172, 'báo cáo', 'vi', 'report', 'noun', 'Một bản tường thuật chi tiết về một sự kiện, tình huống hoặc hoạt động, thường được viết hoặc nói.', 'Cô ấy đã nộp báo cáo doanh số hôm qua.', NULL, '2025-04-15 20:16:55'),
(173, 'email', 'en', 'thư điện tử', 'noun', 'A message sent electronically from one computer user to another.', 'I received an email from the client.', '/ˈiː.meɪl/', '2025-04-15 20:16:55'),
(174, 'thư điện tử', 'vi', 'email', 'noun', 'Một tin nhắn được gửi qua điện tử từ một người dùng máy tính đến người khác.', 'Tôi nhận được thư điện tử từ khách hàng.', NULL, '2025-04-15 20:16:55'),
(175, 'appointment', 'en', 'cuộc hẹn', 'noun', 'An arrangement to meet someone at a particular time and place.', 'I have an appointment with the doctor at 3 PM.', '/əˈpɔɪnt.mənt/', '2025-04-15 20:16:55'),
(176, 'cuộc hẹn', 'vi', 'appointment', 'noun', 'Sự sắp xếp để gặp ai đó tại một thời điểm và địa điểm cụ thể.', 'Tôi có cuộc hẹn với bác sĩ lúc 3 giờ chiều.', NULL, '2025-04-15 20:16:55'),
(177, 'project', 'en', 'dự án', 'noun', 'A planned piece of work that has a specific purpose.', 'The team is working on a new project.', '/ˈprɒdʒ.ekt/', '2025-04-15 20:16:55'),
(178, 'dự án', 'vi', 'project', 'noun', 'Một công việc được lên kế hoạch có mục đích cụ thể.', 'Nhóm đang làm việc cho một dự án mới.', NULL, '2025-04-15 20:16:55'),
(179, 'sales', 'en', 'doanh số', 'noun', 'The activity of selling goods or services, or the amount sold.', 'Our sales increased by 10% this month.', '/seɪlz/', '2025-04-15 20:16:55'),
(180, 'doanh số', 'vi', 'sales', 'noun', 'Hoạt động bán hàng hóa hoặc dịch vụ, hoặc số lượng được bán.', 'Doanh số của chúng tôi tăng 10% trong tháng này.', NULL, '2025-04-15 20:16:55'),
(181, 'team', 'en', 'nhóm', 'noun', 'A group of people who work together to achieve a common goal.', 'The marketing team launched a new campaign.', '/tiːm/', '2025-04-15 20:16:55'),
(182, 'nhóm', 'vi', 'team', 'noun', 'Một nhóm người làm việc cùng nhau để đạt được mục tiêu chung.', 'Nhóm tiếp thị đã khởi động một chiến dịch mới.', NULL, '2025-04-15 20:16:55'),
(183, 'training', 'en', 'đào tạo', 'noun', 'The process of learning the skills needed for a particular job or activity.', 'New employees must attend training sessions.', '/ˈtreɪ.nɪŋ/', '2025-04-15 20:16:55'),
(184, 'đào tạo', 'vi', 'training', 'noun', 'Quá trình học các kỹ năng cần thiết cho một công việc hoặc hoạt động cụ thể.', 'Nhân viên mới phải tham gia các buổi đào tạo.', NULL, '2025-04-15 20:16:55'),
(185, 'document', 'en', 'tài liệu', 'noun', 'A piece of written, printed, or electronic matter that provides information.', 'Please review the document before the meeting.', '/ˈdɒk.jʊ.mənt/', '2025-04-15 20:16:55'),
(186, 'tài liệu', 'vi', 'document', 'noun', 'Một mảnh văn bản, in ấn hoặc điện tử cung cấp thông tin.', 'Vui lòng xem lại tài liệu trước cuộc họp.', NULL, '2025-04-15 20:16:55'),
(187, 'payment', 'en', 'thanh toán', 'noun', 'The act of paying money or the amount paid.', 'The payment is due by the end of the month.', '/ˈpeɪ.mənt/', '2025-04-15 20:16:55'),
(188, 'thanh toán', 'vi', 'payment', 'noun', 'Hành động trả tiền hoặc số tiền được trả.', 'Khoản thanh toán phải được thực hiện trước cuối tháng.', NULL, '2025-04-15 20:16:55'),
(189, 'advertisement', 'en', 'quảng cáo', 'noun', 'A public notice or announcement promoting a product or service.', 'The advertisement appeared in the newspaper.', '/ədˈvɜːr.tɪs.mənt/', '2025-04-15 20:16:55'),
(190, 'quảng cáo', 'vi', 'advertisement', 'noun', 'Một thông báo hoặc công bố công khai để quảng bá sản phẩm hoặc dịch vụ.', 'Quảng cáo đã xuất hiện trên báo.', NULL, '2025-04-15 20:16:55'),
(191, 'delivery', 'en', 'giao hàng', 'noun', 'The act of bringing goods to a person or place.', 'The delivery arrived this morning.', '/dɪˈlɪv.ər.i/', '2025-04-15 20:16:55'),
(192, 'giao hàng', 'vi', 'delivery', 'noun', 'Hành động mang hàng hóa đến một người hoặc địa điểm.', 'Hàng giao đã đến sáng nay.', NULL, '2025-04-15 20:16:55'),
(193, 'feedback', 'en', 'phản hồi', 'noun', 'Information or comments about something, given to improve it.', 'We welcome customer feedback.', '/ˈfiːd.bæk/', '2025-04-15 20:16:55'),
(194, 'phản hồi', 'vi', 'feedback', 'noun', 'Thông tin hoặc ý kiến về một việc gì đó, được đưa ra để cải thiện nó.', 'Chúng tôi hoan nghênh phản hồi từ khách hàng.', NULL, '2025-04-15 20:16:55'),
(195, 'inventory', 'en', 'hàng tồn kho', 'noun', 'A complete list of items or goods in stock.', 'The store checks its inventory monthly.', '/ˈɪn.vən.tər.i/', '2025-04-15 20:16:55'),
(196, 'hàng tồn kho', 'vi', 'inventory', 'noun', 'Danh sách đầy đủ các mặt hàng hoặc hàng hóa trong kho.', 'Cửa hàng kiểm tra hàng tồn kho hàng tháng.', NULL, '2025-04-15 20:16:55'),
(197, 'application', 'en', 'đơn xin', 'noun', 'A formal request, often for a job or position.', 'She submitted a job application.', '/ˌæp.lɪˈkeɪ.ʃən/', '2025-04-15 20:16:55'),
(198, 'đơn xin', 'vi', 'application', 'noun', 'Một yêu cầu chính thức, thường là cho một công việc hoặc vị trí.', 'Cô ấy đã nộp đơn xin việc.', NULL, '2025-04-15 20:16:55'),
(199, 'interview', 'en', 'phỏng vấn', 'noun', 'A formal meeting to assess a candidate for a job or role.', 'He has an interview tomorrow.', '/ˈɪn.tər.vjuː/', '2025-04-15 20:16:55'),
(200, 'phỏng vấn', 'vi', 'interview', 'noun', 'Một cuộc gặp chính thức để đánh giá ứng viên cho một công việc hoặc vai trò.', 'Anh ấy có một cuộc phỏng vấn vào ngày mai.', NULL, '2025-04-15 20:16:55'),
(201, 'department', 'en', 'phòng ban', 'noun', 'A division of a large organization dealing with a specific area.', 'She works in the HR department.', '/dɪˈpɑːrt.mənt/', '2025-04-15 20:16:55'),
(202, 'phòng ban', 'vi', 'department', 'noun', 'Một bộ phận của một tổ chức lớn phụ trách một lĩnh vực cụ thể.', 'Cô ấy làm việc ở phòng nhân sự.', NULL, '2025-04-15 20:16:55'),
(203, 'equipment', 'en', 'thiết bị', 'noun', 'The tools or machines needed for a particular purpose.', 'The office needs new equipment.', '/ɪˈkwɪp.mənt/', '2025-04-15 20:16:55'),
(204, 'thiết bị', 'vi', 'equipment', 'noun', 'Các công cụ hoặc máy móc cần thiết cho một mục đích cụ thể.', 'Văn phòng cần thiết bị mới.', NULL, '2025-04-15 20:16:55'),
(205, 'schedule', 'en', 'kế hoạch', 'noun', 'A plan of activities or tasks to be done at specific times.', 'We need to follow the project schedule.', '/ˈskɛdʒ.uːl/', '2025-04-15 20:16:55'),
(206, 'kế hoạch', 'vi', 'schedule', 'noun', 'Một kế hoạch các hoạt động hoặc nhiệm vụ được thực hiện vào thời điểm cụ thể.', 'Chúng ta cần tuân theo kế hoạch dự án.', NULL, '2025-04-15 20:16:55'),
(207, 'policy', 'en', 'chính sách', 'noun', 'A set of rules or principles adopted by an organization.', 'The company updated its refund policy.', '/ˈpɒl.ə.si/', '2025-04-15 20:16:55'),
(208, 'chính sách', 'vi', 'policy', 'noun', 'Một bộ quy tắc hoặc nguyên tắc được tổ chức áp dụng.', 'Công ty đã cập nhật chính sách hoàn tiền.', NULL, '2025-04-15 20:16:55'),
(209, 'request', 'en', 'yêu cầu', 'noun', 'An act of asking for something formally or politely.', 'Please send your request by email.', '/rɪˈkwest/', '2025-04-15 20:16:55'),
(210, 'yêu cầu', 'vi', 'request', 'noun', 'Hành động yêu cầu một điều gì đó một cách chính thức hoặc lịch sự.', 'Vui lòng gửi yêu cầu của bạn qua thư điện tử.', NULL, '2025-04-15 20:16:55'),
(211, 'service', 'en', 'dịch vụ', 'noun', 'The action of helping or doing work for someone.', 'The restaurant offers excellent service.', '/ˈsɜːr.vɪs/', '2025-04-15 20:16:55'),
(212, 'dịch vụ', 'vi', 'service', 'noun', 'Hành động giúp đỡ hoặc làm việc cho ai đó.', 'Nhà hàng cung cấp dịch vụ tuyệt vời.', NULL, '2025-04-15 20:16:55'),
(213, 'receipt', 'en', 'biên lai', 'noun', 'A document acknowledging that a payment has been made.', 'Keep your receipt for returns.', '/rɪˈsiːt/', '2025-04-15 20:16:55'),
(214, 'biên lai', 'vi', 'receipt', 'noun', 'Một tài liệu xác nhận rằng khoản thanh toán đã được thực hiện.', 'Giữ biên lai của bạn để đổi hàng.', NULL, '2025-04-15 20:16:55'),
(215, 'survey', 'en', 'khảo sát', 'noun', 'A method of gathering information from a group of people.', 'We conducted a customer survey.', '/ˈsɜːr.veɪ/', '2025-04-15 20:16:55'),
(216, 'khảo sát', 'vi', 'survey', 'noun', 'Phương pháp thu thập thông tin từ một nhóm người.', 'Chúng tôi đã thực hiện một khảo sát khách hàng.', NULL, '2025-04-15 20:16:55'),
(217, 'expense', 'en', 'chi phí', 'noun', 'The cost required for something.', 'Travel expenses will be reimbursed.', '/ɪkˈspens/', '2025-04-15 20:16:55'),
(218, 'chi phí', 'vi', 'expense', 'noun', 'Chi phí cần thiết cho một thứ gì đó.', 'Chi phí đi lại sẽ được hoàn trả.', NULL, '2025-04-15 20:16:55'),
(219, 'task', 'en', 'nhiệm vụ', 'noun', 'A piece of work to be done or undertaken.', 'Completing the report is my main task today.', '/tæsk/', '2025-04-15 20:16:55'),
(220, 'nhiệm vụ', 'vi', 'task', 'noun', 'Một công việc cần được thực hiện hoặc đảm nhận.', 'Hoàn thành báo cáo là nhiệm vụ chính của tôi hôm nay.', NULL, '2025-04-15 20:16:55'),
(221, 'agenda', 'en', 'chương trình nghị sự', 'noun', 'A list of items to be discussed at a meeting.', 'The agenda for the meeting was sent yesterday.', '/əˈdʒen.də/', '2025-04-15 20:23:25'),
(222, 'chương trình nghị sự', 'vi', 'agenda', 'noun', 'Danh sách các mục sẽ được thảo luận trong một cuộc họp.', 'Chương trình nghị sự cho cuộc họp đã được gửi hôm qua.', NULL, '2025-04-15 20:23:25'),
(223, 'branch', 'en', 'chi nhánh', 'noun', 'A division or office of a larger organization.', 'The bank opened a new branch downtown.', '/bræntʃ/', '2025-04-15 20:23:25'),
(224, 'chi nhánh', 'vi', 'branch', 'noun', 'Một bộ phận hoặc văn phòng của một tổ chức lớn hơn.', 'Ngân hàng đã mở một chi nhánh mới ở trung tâm.', NULL, '2025-04-15 20:23:25'),
(225, 'client', 'en', 'thân chủ', 'noun', 'A person or organization using the services of a professional.', 'We met with a new client today.', '/ˈklaɪ.ənt/', '2025-04-15 20:23:25'),
(226, 'thân chủ', 'vi', 'client', 'noun', 'Một cá nhân hoặc tổ chức sử dụng dịch vụ của một chuyên gia.', 'Chúng tôi đã gặp một thân chủ mới hôm nay.', NULL, '2025-04-15 20:23:25'),
(227, 'fare', 'en', 'giá vé', 'noun', 'The money paid for a journey on public transport.', 'The bus fare increased last month.', '/feər/', '2025-04-15 20:23:25'),
(228, 'giá vé', 'vi', 'fare', 'noun', 'Số tiền trả cho một chuyến đi trên phương tiện công cộng.', 'Giá vé xe buýt đã tăng tháng trước.', NULL, '2025-04-15 20:23:25'),
(229, 'luggage', 'en', 'hành lý', 'noun', 'Bags and other items a traveler carries.', 'Please check your luggage at the counter.', '/ˈlʌɡ.ɪdʒ/', '2025-04-15 20:23:25'),
(230, 'hành lý', 'vi', 'luggage', 'noun', 'Túi xách và các vật dụng khác mà một du khách mang theo.', 'Vui lòng kiểm tra hành lý của bạn tại quầy.', NULL, '2025-04-15 20:23:25'),
(231, 'memo', 'en', 'bản ghi nhớ', 'noun', 'A short written message used in a business.', 'She sent a memo to all staff members.', '/ˈmem.oʊ/', '2025-04-15 20:23:25'),
(232, 'bản ghi nhớ', 'vi', 'memo', 'noun', 'Một thông điệp ngắn bằng văn bản được sử dụng trong kinh doanh.', 'Cô ấy gửi bản ghi nhớ cho tất cả nhân viên.', NULL, '2025-04-15 20:23:25'),
(233, 'order', 'en', 'đơn hàng', 'noun', 'A request for goods or services to be supplied.', 'The company received a large order today.', '/ˈɔːr.dər/', '2025-04-15 20:23:25'),
(234, 'đơn hàng', 'vi', 'order', 'noun', 'Yêu cầu cung cấp hàng hóa hoặc dịch vụ.', 'Công ty nhận được một đơn hàng lớn hôm nay.', NULL, '2025-04-15 20:23:25'),
(235, 'schedule', 'en', 'thời gian biểu', 'noun', 'A list of times when events or activities are planned.', 'The train schedule changed this week.', '/ˈskɛdʒ.uːl/', '2025-04-15 20:23:25'),
(236, 'thời gian biểu', 'vi', 'schedule', 'noun', 'Danh sách thời gian khi các sự kiện hoặc hoạt động được lên kế hoạch.', 'Thời gian biểu tàu hỏa đã thay đổi tuần này.', NULL, '2025-04-15 20:23:25'),
(237, 'supplier', 'en', 'nhà cung cấp', 'noun', 'A person or company that provides goods or services.', 'We need a reliable supplier for paper.', '/səˈplaɪ.ər/', '2025-04-15 20:23:25'),
(238, 'nhà cung cấp', 'vi', 'supplier', 'noun', 'Một cá nhân hoặc công ty cung cấp hàng hóa hoặc dịch vụ.', 'Chúng tôi cần một nhà cung cấp đáng tin cậy cho giấy.', NULL, '2025-04-15 20:23:25'),
(239, 'target', 'en', 'mục tiêu', 'noun', 'A goal or objective to be achieved.', 'Our sales target is $10,000 this month.', '/ˈtɑːr.ɡɪt/', '2025-04-15 20:23:25'),
(240, 'mục tiêu', 'vi', 'target', 'noun', 'Một mục đích hoặc mục tiêu cần đạt được.', 'Mục tiêu doanh số của chúng tôi là 10.000 đô la tháng này.', NULL, '2025-04-15 20:23:25'),
(241, 'account', 'en', 'tài khoản', 'noun', 'A record of financial transactions or a user profile.', 'Please check your bank account balance.', '/əˈkaʊnt/', '2025-04-15 20:23:25'),
(242, 'tài khoản', 'vi', 'account', 'noun', 'Bản ghi các giao dịch tài chính hoặc hồ sơ người dùng.', 'Vui lòng kiểm tra số dư tài khoản ngân hàng của bạn.', NULL, '2025-04-15 20:23:25'),
(243, 'bonus', 'en', 'tiền thưởng', 'noun', 'Extra money given for good performance.', 'Employees received a holiday bonus.', '/ˈboʊ.nəs/', '2025-04-15 20:23:25'),
(244, 'tiền thưởng', 'vi', 'bonus', 'noun', 'Số tiền bổ sung được trao cho hiệu suất tốt.', 'Nhân viên nhận được tiền thưởng dịp lễ.', NULL, '2025-04-15 20:23:25'),
(245, 'complaint', 'en', 'khiếu nại', 'noun', 'An expression of dissatisfaction.', 'The store handled the customer’s complaint quickly.', '/kəmˈpleɪnt/', '2025-04-15 20:23:25'),
(246, 'khiếu nại', 'vi', 'complaint', 'noun', 'Sự bày tỏ sự không hài lòng.', 'Cửa hàng đã xử lý khiếu nại của khách hàng nhanh chóng.', NULL, '2025-04-15 20:23:25'),
(247, 'discount', 'en', 'giảm giá', 'noun', 'A reduction in the usual price.', 'They offered a 20% discount on all items.', '/ˈdɪs.kaʊnt/', '2025-04-15 20:23:25'),
(248, 'giảm giá', 'vi', 'discount', 'noun', 'Sự giảm giá so với giá thông thường.', 'Họ cung cấp giảm giá 20% cho tất cả mặt hàng.', NULL, '2025-04-15 20:23:25'),
(249, 'form', 'en', 'biểu mẫu', 'noun', 'A document with spaces to fill in information.', 'Please complete the registration form.', '/fɔːrm/', '2025-04-15 20:23:25'),
(250, 'biểu mẫu', 'vi', 'form', 'noun', 'Một tài liệu có khoảng trống để điền thông tin.', 'Vui lòng điền vào biểu mẫu đăng ký.', NULL, '2025-04-15 20:23:25'),
(251, 'guest', 'en', 'khách', 'noun', 'A person invited to a place or event.', 'The hotel welcomed 50 guests today.', '/ɡest/', '2025-04-15 20:23:25'),
(252, 'khách', 'vi', 'guest', 'noun', 'Một người được mời đến một địa điểm hoặc sự kiện.', 'Khách sạn đã đón 50 khách hôm nay.', NULL, '2025-04-15 20:23:25'),
(253, 'itinerary', 'en', 'lịch trình', 'noun', 'A detailed plan for a journey.', 'The travel agent sent us the itinerary.', '/aɪˈtɪn.ər.er.i/', '2025-04-15 20:23:25'),
(254, 'lịch trình', 'vi', 'itinerary', 'noun', 'Kế hoạch chi tiết cho một chuyến đi.', 'Công ty du lịch đã gửi cho chúng tôi lịch trình.', NULL, '2025-04-15 20:23:25'),
(255, 'notice', 'en', 'thông báo', 'noun', 'A written or spoken announcement.', 'A notice was posted about the office closure.', '/ˈnoʊ.tɪs/', '2025-04-15 20:23:25'),
(256, 'thông báo', 'vi', 'notice', 'noun', 'Một thông báo bằng văn bản hoặc lời nói.', 'Một thông báo đã được đăng về việc đóng cửa văn phòng.', NULL, '2025-04-15 20:23:25'),
(257, 'product', 'en', 'sản phẩm', 'noun', 'An item made or offered for sale.', 'The company launched a new product.', '/ˈprɒd.ʌkt/', '2025-04-15 20:23:25'),
(258, 'sản phẩm', 'vi', 'product', 'noun', 'Một mặt hàng được sản xuất hoặc cung cấp để bán.', 'Công ty đã ra mắt một sản phẩm mới.', NULL, '2025-04-15 20:23:25'),
(259, 'refund', 'en', 'hoàn tiền', 'noun', 'Money returned when goods are unsatisfactory.', 'She requested a refund for the defective item.', '/ˈriː.fʌnd/', '2025-04-15 20:23:25'),
(260, 'hoàn tiền', 'vi', 'refund', 'noun', 'Số tiền được trả lại khi hàng hóa không đạt yêu cầu.', 'Cô ấy yêu cầu hoàn tiền cho mặt hàng bị lỗi.', NULL, '2025-04-15 20:23:25'),
(261, 'repair', 'en', 'sửa chữa', 'noun', 'The act of fixing something broken.', 'The machine needs urgent repair.', '/rɪˈpeər/', '2025-04-15 20:23:25'),
(262, 'sửa chữa', 'vi', 'repair', 'noun', 'Hành động sửa chữa một thứ gì đó bị hỏng.', 'Máy móc cần sửa chữa khẩn cấp.', NULL, '2025-04-15 20:23:25'),
(263, 'shift', 'en', 'ca làm việc', 'noun', 'A period of time worked by a group of workers.', 'He works the night shift.', '/ʃɪft/', '2025-04-15 20:23:25'),
(264, 'ca làm việc', 'vi', 'shift', 'noun', 'Một khoảng thời gian làm việc của một nhóm công nhân.', 'Anh ấy làm ca đêm.', NULL, '2025-04-15 20:23:25'),
(265, 'stock', 'en', 'cổ phiếu', 'noun', 'Shares of a company or goods in a store.', 'The store is low on stock.', '/stɒk/', '2025-04-15 20:23:25'),
(266, 'cổ phiếu', 'vi', 'stock', 'noun', 'Cổ phần của một công ty hoặc hàng hóa trong cửa hàng.', 'Cửa hàng đang thiếu hàng.', NULL, '2025-04-15 20:23:25'),
(267, 'update', 'en', 'cập nhật', 'noun', 'New or more recent information.', 'The manager gave an update on the project.', '/ˈʌp.deɪt/', '2025-04-15 20:23:25'),
(268, 'cập nhật', 'vi', 'update', 'noun', 'Thông tin mới hoặc gần đây hơn.', 'Quản lý đã cung cấp cập nhật về dự án.', NULL, '2025-04-15 20:23:25'),
(269, 'vacancy', 'en', 'chỗ trống', 'noun', 'An available job or room.', 'The hotel has no vacancies this week.', '/ˈveɪ.kən.si/', '2025-04-15 20:23:25'),
(270, 'chỗ trống', 'vi', 'vacancy', 'noun', 'Một công việc hoặc phòng còn trống.', 'Khách sạn không còn chỗ trống tuần này.', NULL, '2025-04-15 20:23:25'),
(271, 'arrival', 'en', 'sự đến', 'noun', 'The act of reaching a destination.', 'The flight’s arrival is delayed.', '/əˈraɪ.vəl/', '2025-04-15 20:23:25'),
(272, 'sự đến', 'vi', 'arrival', 'noun', 'Hành động đến một điểm đích.', 'Sự đến của chuyến bay bị trì hoãn.', NULL, '2025-04-15 20:23:25'),
(273, 'benefit', 'en', 'lợi ích', 'noun', 'An advantage or profit gained.', 'Health insurance is a company benefit.', '/ˈben.ɪ.fɪt/', '2025-04-15 20:23:25'),
(274, 'lợi ích', 'vi', 'benefit', 'noun', 'Một lợi thế hoặc lợi nhuận đạt được.', 'Bảo hiểm y tế là một lợi ích của công ty.', NULL, '2025-04-15 20:23:25'),
(275, 'catalog', 'en', 'danh mục', 'noun', 'A list of items, often with descriptions.', 'The catalog includes all our products.', '/ˈkæt.ə.lɒɡ/', '2025-04-15 20:23:25'),
(276, 'danh mục', 'vi', 'catalog', 'noun', 'Danh sách các mặt hàng, thường kèm mô tả.', 'Danh mục bao gồm tất cả sản phẩm của chúng tôi.', NULL, '2025-04-15 20:23:25'),
(277, 'invoice', 'en', 'phiếu thanh toán', 'noun', 'A request for payment listing services or goods.', 'The invoice must be paid by Friday.', '/ˈɪn.vɔɪs/', '2025-04-15 20:23:25'),
(278, 'phiếu thanh toán', 'vi', 'invoice', 'noun', 'Yêu cầu thanh toán liệt kê dịch vụ hoặc hàng hóa.', 'Phiếu thanh toán phải được thanh toán trước thứ Sáu.', NULL, '2025-04-15 20:23:25'),
(279, 'permit', 'en', 'giấy phép', 'noun', 'An official document allowing something.', 'You need a permit to park here.', '/ˈpɜːr.mɪt/', '2025-04-15 20:23:25'),
(280, 'giấy phép', 'vi', 'permit', 'noun', 'Một tài liệu chính thức cho phép một việc gì đó.', 'Bạn cần giấy phép để đỗ xe ở đây.', NULL, '2025-04-15 20:23:25'),
(281, 'proposal', 'en', 'đề xuất', 'noun', 'A plan or suggestion put forward for consideration.', 'The proposal was accepted by the board.', '/prəˈpoʊ.zəl/', '2025-04-15 20:23:25'),
(282, 'đề xuất', 'vi', 'proposal', 'noun', 'Một kế hoạch hoặc gợi ý được đưa ra để xem xét.', 'Đề xuất đã được hội đồng chấp thuận.', NULL, '2025-04-15 20:23:25'),
(283, 'response', 'en', 'phản ứng', 'noun', 'An answer or reaction to something.', 'We await your response to the offer.', '/rɪˈspɒns/', '2025-04-15 20:23:25'),
(284, 'phản ứng', 'vi', 'response', 'noun', 'Một câu trả lời hoặc phản ứng đối với một điều gì đó.', 'Chúng tôi đang chờ phản ứng của bạn đối với đề nghị.', NULL, '2025-04-15 20:23:25'),
(285, 'safety', 'en', 'an toàn', 'noun', 'The condition of being protected from harm.', 'Safety is our top priority.', '/ˈseɪf.ti/', '2025-04-15 20:23:25'),
(286, 'an toàn', 'vi', 'safety', 'noun', 'Tình trạng được bảo vệ khỏi nguy hại.', 'An toàn là ưu tiên hàng đầu của chúng tôi.', NULL, '2025-04-15 20:23:25'),
(287, 'ticket', 'en', 'vé', 'noun', 'A piece of paper or card giving access to an event or transport.', 'I lost my train ticket.', '/ˈtɪk.ɪt/', '2025-04-15 20:23:25'),
(288, 'vé', 'vi', 'ticket', 'noun', 'Một mảnh giấy hoặc thẻ cho phép tham gia sự kiện hoặc đi lại.', 'Tôi làm mất vé tàu.', NULL, '2025-04-15 20:23:25'),
(289, 'warranty', 'en', 'bảo hành', 'noun', 'A guarantee that a product will work as expected.', 'The laptop comes with a one-year warranty.', '/ˈwɒr.ən.ti/', '2025-04-15 20:23:25'),
(290, 'bảo hành', 'vi', 'warranty', 'noun', 'Sự đảm bảo rằng sản phẩm sẽ hoạt động như kỳ vọng.', 'Máy tính xách tay được bảo hành một năm.', NULL, '2025-04-15 20:23:25'),
(291, 'audit', 'en', 'kiểm toán', 'noun', 'An official inspection of financial records.', 'The company underwent an audit last month.', '/ˈɔː.dɪt/', '2025-04-15 20:23:25'),
(292, 'kiểm toán', 'vi', 'audit', 'noun', 'Việc kiểm tra chính thức các hồ sơ tài chính.', 'Công ty đã trải qua một cuộc kiểm toán tháng trước.', NULL, '2025-04-15 20:23:25'),
(293, 'campaign', 'en', 'chiến dịch', 'noun', 'A planned set of activities to achieve a goal.', 'The marketing campaign was a success.', '/kæmˈpeɪn/', '2025-04-15 20:23:25'),
(294, 'chiến dịch', 'vi', 'campaign', 'noun', 'Một tập hợp các hoạt động được lên kế hoạch để đạt được mục tiêu.', 'Chiến dịch tiếp thị đã thành công.', NULL, '2025-04-15 20:23:25'),
(295, 'condition', 'en', 'điều kiện', 'noun', 'The state of something or terms of an agreement.', 'The car is in good condition.', '/kənˈdɪʃ.ən/', '2025-04-15 20:23:25'),
(296, 'điều kiện', 'vi', 'condition', 'noun', 'Tình trạng của một thứ gì đó hoặc các điều khoản của một thỏa thuận.', 'Chiếc xe ở trong tình trạng tốt.', NULL, '2025-04-15 20:23:25'),
(297, 'estimate', 'en', 'ước tính', 'noun', 'An approximate calculation of cost or time.', 'The estimate for repairs is $200.', '/ˈes.tɪ.meɪt/', '2025-04-15 20:23:25'),
(298, 'ước tính', 'vi', 'estimate', 'noun', 'Một tính toán gần đúng về chi phí hoặc thời gian.', 'Ước tính cho việc sửa chữa là 200 đô la.', NULL, '2025-04-15 20:23:25'),
(299, 'facility', 'en', 'cơ sở', 'noun', 'A place or equipment provided for a purpose.', 'The new facility has modern equipment.', '/fəˈsɪl.ɪ.ti/', '2025-04-15 20:23:25'),
(300, 'cơ sở', 'vi', 'facility', 'noun', 'Một địa điểm hoặc thiết bị được cung cấp cho một mục đích.', 'Cơ sở mới có thiết bị hiện đại.', NULL, '2025-04-15 20:23:25'),
(301, 'instruction', 'en', 'hướng dẫn', 'noun', 'Detailed information on how to do something.', 'Follow the instructions on the package.', '/ɪnˈstrʌk.ʃən/', '2025-04-15 20:23:25'),
(302, 'hướng dẫn', 'vi', 'instruction', 'noun', 'Thông tin chi tiết về cách làm một việc gì đó.', 'Làm theo hướng dẫn trên bao bì.', NULL, '2025-04-15 20:23:25'),
(303, 'market', 'en', 'thị trường', 'noun', 'A place or system for buying and selling goods.', 'The market for smartphones is growing.', '/ˈmɑːr.kɪt/', '2025-04-15 20:23:25'),
(304, 'thị trường', 'vi', 'market', 'noun', 'Nơi hoặc hệ thống để mua bán hàng hóa.', 'Thị trường điện thoại thông minh đang phát triển.', NULL, '2025-04-15 20:23:25');
INSERT INTO `dictionary` (`id`, `word`, `language`, `translation`, `word_type`, `detailed_explanation`, `example`, `pronunciation`, `created_at`) VALUES
(305, 'option', 'en', 'lựa chọn', 'noun', 'A thing that may be chosen.', 'You have the option to cancel the order.', '/ˈɒp.ʃən/', '2025-04-15 20:23:25'),
(306, 'lựa chọn', 'vi', 'option', 'noun', 'Một thứ có thể được chọn.', 'Bạn có lựa chọn hủy đơn hàng.', NULL, '2025-04-15 20:23:25'),
(307, 'package', 'en', 'gói', 'noun', 'A wrapped item or a set of services offered together.', 'The package was delivered to the office.', '/ˈpæk.ɪdʒ/', '2025-04-15 20:23:25'),
(308, 'gói', 'vi', 'package', 'noun', 'Một vật được bọc hoặc một tập hợp dịch vụ được cung cấp cùng nhau.', 'Gói hàng đã được giao đến văn phòng.', NULL, '2025-04-15 20:23:25'),
(309, 'receipt', 'en', 'hóa đơn', 'noun', 'A document showing payment details.', 'The receipt shows the purchase date.', '/rɪˈsiːt/', '2025-04-15 20:23:25'),
(310, 'hóa đơn', 'vi', 'receipt', 'noun', 'Một tài liệu hiển thị chi tiết thanh toán.', 'Hóa đơn hiển thị ngày mua.', NULL, '2025-04-15 20:23:25'),
(311, 'review', 'en', 'đánh giá', 'noun', 'An evaluation or assessment.', 'The manager asked for a performance review.', '/rɪˈvjuː/', '2025-04-15 20:23:25'),
(312, 'đánh giá', 'vi', 'review', 'noun', 'Một sự đánh giá hoặc xem xét.', 'Quản lý yêu cầu một đánh giá hiệu suất.', NULL, '2025-04-15 20:23:25'),
(313, 'section', 'en', 'phần', 'noun', 'A distinct part of something.', 'The store has a new clothing section.', '/ˈsek.ʃən/', '2025-04-15 20:23:25'),
(314, 'phần', 'vi', 'section', 'noun', 'Một phần riêng biệt của một thứ gì đó.', 'Cửa hàng có một phần quần áo mới.', NULL, '2025-04-15 20:23:25'),
(315, 'support', 'en', 'hỗ trợ', 'noun', 'Help or assistance provided.', 'Contact customer support for help.', '/səˈpɔːrt/', '2025-04-15 20:23:25'),
(316, 'hỗ trợ', 'vi', 'support', 'noun', 'Sự giúp đỡ hoặc hỗ trợ được cung cấp.', 'Liên hệ hỗ trợ khách hàng để được giúp đỡ.', NULL, '2025-04-15 20:23:25'),
(317, 'transfer', 'en', 'chuyển khoản', 'noun', 'The act of moving money or duties to another place.', 'The bank transfer was completed today.', '/ˈtræns.fɜːr/', '2025-04-15 20:23:25'),
(318, 'chuyển khoản', 'vi', 'transfer', 'noun', 'Hành động chuyển tiền hoặc nhiệm vụ sang nơi khác.', 'Việc chuyển khoản ngân hàng đã hoàn tất hôm nay.', NULL, '2025-04-15 20:23:25'),
(319, 'vehicle', 'en', 'phương tiện', 'noun', 'A means of transport, such as a car or truck.', 'The company bought a new vehicle.', '/ˈviː.ɪ.kəl/', '2025-04-15 20:23:25'),
(320, 'phương tiện', 'vi', 'vehicle', 'noun', 'Phương tiện vận chuyển, như xe hơi hoặc xe tải.', 'Công ty đã mua một phương tiện mới.', NULL, '2025-04-15 20:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `audio_path` varchar(255) DEFAULT NULL,
  `correct_option` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_lessons`
--

CREATE TABLE `sub_lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `content_file` varchar(255) DEFAULT NULL,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_lessons`
--

INSERT INTO `sub_lessons` (`id`, `course_id`, `title`, `description`, `video_url`, `content_file`, `order_number`) VALUES
(7, 1, 'Unit 1: Alphalbet Spelling', 'Bài học mở đầu nắm vững cơ bản', 'https://www.youtube.com/watch?v=ktmQMoWFb00', 'uploads/Unit 1. Alphalbet (Spelling) - Listening.pdf', 1),
(8, 1, '\nUnit 2: Country and nationalities', 'Học từ vựng thường gặp trong phần Listening và Reading.', 'https://www.youtube.com/watch?v=hZMmgCzydWw&t=1s', 'uploads/Unit 2.Country and nationalities (Listening).pdf', 2),
(9, 1, '\nUnit 3: Number', 'Ôn tập các điểm ngữ pháp quan trọng cho TOEIC.', 'https://www.youtube.com/watch?v=HmMkTZEd9CA&t=3s', 'uploads/Unit 3.Number Listening.pdf', 3),
(13, 1, '\nUnit 4: Color', 'học thêm nâng cao', 'https://www.youtube.com/watch?v=vtqiqUOSZww&t=1s', 'uploads/Unit 4.Color - Listening.pdf', 4),
(14, 1, 'Unit 5: Clothes', NULL, 'https://www.youtube.com/watch?v=enD5fKb8DLk&t=9s', 'uploads/Unit5 clothes - Listening.pdf\r\n', 5),
(15, 1, 'Unit 6. Body Part', NULL, 'https://www.youtube.com/watch?v=TpHYSixABM8', 'uploads/Unit 6. Body Parts - Listening.pdf', 6),
(16, 1, 'Unit 7.Family Members', NULL, 'https://www.youtube.com/watch?v=0rIx6vDUovg', 'uploads/Unit 7.Family Members - Listening.pdf', 7),
(17, 1, 'Unit 8.Fruits', NULL, 'https://www.youtube.com/watch?v=Dm3YJJR3qss', 'uploads/Unit 8.Fruits-Listening.pdf', 8),
(18, 1, 'Unit 9.House', NULL, 'https://www.youtube.com/watch?v=uECcS7XbZcc', 'uploads/Unit 9.House-Listening.pdf', 9),
(25, 12, 'Bài 1 thứ bạn cần thay đổi english study', NULL, 'https://www.youtube.com/watch?v=6ZcGSYn9Ark&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL', NULL, 1),
(26, 12, 'Bài 2 dẫn chứng cách thay đổi', NULL, 'https://www.youtube.com/watch?v=O75JVbpwpp8&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=2', NULL, 2),
(27, 12, 'Bài 3 tất cả cần biết về lý thuyết', NULL, 'https://www.youtube.com/watch?v=v4Bl4aI6f5s&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=3', NULL, 3),
(28, 12, 'Bài 4 làm điều này mỗi khi xem tiếng anh', NULL, 'https://www.youtube.com/watch?v=xDV2eifPZFo&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=4', NULL, 4),
(29, 12, 'Bài 5 cách học từ vựng hiệu quả', NULL, 'https://www.youtube.com/watch?v=KegexEwaJrI&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=5', NULL, 5),
(30, 12, 'Bài 6 cách ngấm từ mới hiệu quả', NULL, 'https://www.youtube.com/watch?v=PSAgZaLdBFA&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=6', NULL, 6),
(31, 12, 'Bài 7 cụ thể hơn về các loại từ', NULL, 'https://www.youtube.com/watch?v=febUSUwSfEI&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=7', NULL, 7),
(32, 12, 'Bài 8 bài tập và cách ứng dụng', NULL, 'https://www.youtube.com/watch?v=VcF7iFRsvAI&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=8', NULL, 8),
(33, 12, 'Bài 9 cách tiếp cận tiếng anh', NULL, 'https://www.youtube.com/watch?v=RTqh10Lh-8E&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=9', NULL, 9),
(34, 12, 'Bài 10 speaking phát âm và giọng điệu', NULL, 'https://www.youtube.com/watch?v=ouD3xtiaSxQ&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=10', NULL, 10),
(35, 12, 'Bài 11 chủ ngữ và cách viết tắt', NULL, 'https://www.youtube.com/watch?v=8IM8TytjzeE&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=11', NULL, 11),
(36, 12, 'Bài 12 bài tập về ứng dụng ngữ pháp', NULL, 'https://www.youtube.com/watch?v=AgfM03cs4js&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=12', NULL, 12),
(37, 12, 'Bài 13 phương pháp so sánh tìm lỗi sai', NULL, 'https://www.youtube.com/watch?v=vKlvb7AJDRA&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=14', NULL, 13),
(38, 12, 'Bài 14 cách áp dụng phương pháp ấy', NULL, 'https://www.youtube.com/watch?v=zkI94yET09I&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=14', NULL, 14),
(39, 12, 'Bài 15 cách học kỹ năng đọc', NULL, 'https://www.youtube.com/watch?v=-ntM4XiGE3A&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=15', NULL, 15),
(40, 12, 'Bài 16 bài tập về đọc viết', NULL, 'https://www.youtube.com/watch?v=NcFx_pNclHU&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=16', NULL, 16),
(41, 12, 'Bài 17 cách nghĩ về việc học', NULL, 'https://www.youtube.com/watch?v=tpy2-uevmOc&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=17', NULL, 17),
(42, 12, 'Bài 18 thay đổi suy nghĩ', NULL, 'https://www.youtube.com/watch?v=pL412bTUgY0&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=18', NULL, 18),
(43, 12, 'Bài 19 phải có được điều này', NULL, 'https://www.youtube.com/watch?v=JmYjURcL0z8&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=19', NULL, 19),
(44, 12, 'Bài 20 cách học kỹ năng viết', NULL, 'https://www.youtube.com/watch?v=03BynmgIafk&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=20', NULL, 20),
(45, 12, 'Bài 21 bài tập về viết', NULL, 'https://www.youtube.com/watch?v=kutOgT1q04o&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=21', NULL, 21),
(46, 12, 'Bài 22 tìm ra phiên bản lý tưởng', NULL, 'https://www.youtube.com/watch?v=R6b2eTWK4pQ&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=22', NULL, 22),
(47, 12, 'Bài 23 cách luyện tập 4 kỹ năng', NULL, 'https://www.youtube.com/watch?v=D6Sxx7ro8xw&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=23', NULL, 23),
(48, 12, 'Bài 24 cách học Ielts', NULL, 'https://www.youtube.com/watch?v=sEV3OHdFrGY&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=26', NULL, 24),
(49, 12, 'Bài 25 tư duy học hiệu quả', NULL, 'https://www.youtube.com/watch?v=xQtIuRNpPMU&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=24', NULL, 25),
(50, 12, 'Bài 26 bài học cuối cùng', NULL, 'https://www.youtube.com/watch?v=6XsZgYeN_bc&list=PLBmCelJnz3mQdpCy_OOvmMFbt5kceOVaL&index=25', NULL, 26),
(51, 14, 'bai 1', 'bai test 1', 'https://www.youtube.com/watch?v=xEbH-yFMljU', '../Uploads/1745766210_3674e534b26f409092cb019f93275d2d.pdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sub_lesson_tests`
--

CREATE TABLE `sub_lesson_tests` (
  `id` int(11) NOT NULL,
  `sub_lesson_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_lesson_tests`
--

INSERT INTO `sub_lesson_tests` (`id`, `sub_lesson_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(10, 7, '3.Look at Layla. _____________ is happy.', 'They', 'We', 'She', 'He', 'C'),
(11, 7, '4.He __________ in New York.', 'eat', 'read', 'go', 'finish', 'C'),
(12, 7, '5.He __________ books in the evenings.', 'eat', 'read', 'go', 'finish', 'B'),
(13, 7, '6.He __________ lunch at 12 o’clock.', 'eat', 'read', 'go', 'finish', 'A'),
(14, 7, '7. ................... they from South America?', 'are', 'is', 'do', 'don\'t', 'A'),
(15, 7, '8.________ are police officers.', 'They', 'We', 'She', 'He', 'A'),
(16, 7, '9.Amy is 9 years old. ________ is a student.', 'They', 'We', 'She', 'He', 'C'),
(17, 7, '10.I am not at school.', 'I’m at home', 'He’s in the garden.', 'It’s Tuesday.', 'They’re apples.', 'A'),
(18, 8, 'Quốc gia nào có quốc tịch là British?', 'Britain', 'France', 'Spain', 'Germany', 'A'),
(19, 8, 'Người đến từ France gọi là gì?', 'French', 'Francean', 'Francia', 'Franceish', 'A'),
(20, 8, 'Quốc tịch của người Trung Quốc là gì?', 'Chinese', 'Chinanese', 'Chinish', 'Chine', 'A'),
(21, 8, '“Japanese” là quốc tịch của nước nào?', 'Korea', 'Vietnam', 'Japan', 'China', 'C'),
(22, 8, '“Vietnamese” là quốc tịch của nước nào?', 'Singapore', 'Vietnam', 'India', 'Malaysia', 'B'),
(23, 8, '“Australian” đến từ nước nào?', 'Austria', 'America', 'Australia', 'Argentina', 'C'),
(24, 8, 'Người đến từ Spain được gọi là gì?', 'Spainish', 'Spaniard', 'Spanish', 'Spanic', 'C'),
(25, 8, 'Người đến từ Russia gọi là gì?', 'Russian', 'Russish', 'Russan', 'Rushan', 'A'),
(26, 8, 'Singaporean là quốc tịch của quốc gia nào?', 'Singapore', 'Vietnam', 'Thailand', 'Malaysia', 'A'),
(27, 8, 'Người đến từ nước Đức gọi là gì?', 'Germanian', 'German', 'Germish', 'Deutsch', 'B'),
(28, 9, 'Số “mười một” trong tiếng Anh là gì?', 'eleven', 'twelve', 'ten', 'thirteen', 'A'),
(29, 9, 'Số “mười bốn” là gì?', 'fourteen', 'forty', 'fourty', 'fourteenth', 'A'),
(30, 9, 'Số “hai mươi” viết thế nào?', 'twenty', 'twelve', 'tenth', 'twenteen', 'A'),
(31, 9, 'Số “mười ba” trong tiếng Anh là gì?', 'thirteen', 'thirty', 'thirty-three', 'three-teen', 'A'),
(32, 9, 'Số “ba mươi” là gì?', 'thirteen', 'thirty', 'three-ten', 'three-teen', 'B'),
(33, 9, 'Số “bốn mươi” là gì?', 'fourteen', 'forty', 'fourty', 'four-ten', 'B'),
(34, 9, 'Số “năm mươi” là gì?', 'fifty', 'fivety', 'five-teen', 'fifteen', 'A'),
(35, 9, 'Số “mười lăm” là gì?', 'fifteen', 'fifty', 'five-teen', 'fiveteen', 'A'),
(36, 9, 'Số “mười bảy” là gì?', 'seventeen', 'seventy', 'seventen', 'seventine', 'A'),
(37, 9, 'Số “sáu mươi” là gì?', 'sixteen', 'sixty', 'sixten', 'six-ten', 'B'),
(38, 9, 'Số “bảy mươi” là gì?', 'seventy', 'seventeen', 'seven-ten', 'seven-teen', 'A'),
(39, 9, 'Số “tám mươi” là gì?', 'eightteen', 'eighteen', 'eighty', 'eight-ten', 'C'),
(40, 9, 'Số “chín mươi” là gì?', 'ninety', 'nineteen', 'nine-ten', 'ninty', 'A'),
(41, 9, 'Số “mười chín” là gì?', 'nineteen', 'ninty', 'ninety', 'nine-teen', 'A'),
(42, 9, 'Số “mười hai” là gì?', 'twelfth', 'twelve', 'twenty', 'twelf', 'B'),
(43, 9, 'Số “mười sáu” là gì?', 'sixteen', 'sixty', 'six-teen', 'six-tin', 'A'),
(44, 9, 'Số “tám” là gì?', 'eighth', 'eighty', 'eight', 'ate', 'C'),
(45, 9, 'Số “bốn” là gì?', 'four', 'fourth', 'forth', 'for', 'A'),
(46, 9, 'Số “chín” là gì?', 'nine', 'ninth', 'ninty', 'nina', 'A'),
(47, 9, 'Số “hai mươi mốt” là gì?', 'twenty-one', 'twenty-one-th', 'two-one', 'twenteen-one', 'A'),
(66, 51, 'con cho co may chan', '1', '2', '3', '4', 'D');

-- --------------------------------------------------------

--
-- Table structure for table `sub_lesson_test_results`
--

CREATE TABLE `sub_lesson_test_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sub_lesson_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `completed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_lesson_test_results`
--

INSERT INTO `sub_lesson_test_results` (`id`, `user_id`, `sub_lesson_id`, `score`, `total_questions`, `completed_at`) VALUES
(1, 5, 7, 1, 1, '2025-04-12 03:35:56'),
(2, 5, 7, 0, 1, '2025-04-12 03:36:22'),
(3, 5, 7, 1, 1, '2025-04-12 03:36:27'),
(4, 5, 7, 1, 1, '2025-04-12 03:36:27'),
(5, 5, 7, 1, 1, '2025-04-12 03:42:30'),
(6, 5, 7, 1, 1, '2025-04-12 03:51:20'),
(7, 5, 7, 1, 1, '2025-04-12 05:41:19'),
(8, 5, 7, 1, 1, '2025-04-13 20:59:09'),
(9, 5, 7, 1, 1, '2025-04-14 00:18:21'),
(10, 5, 7, 1, 1, '2025-04-14 01:10:56'),
(11, 5, 7, 0, 1, '2025-04-14 01:18:07'),
(12, 5, 7, 1, 1, '2025-04-14 01:18:19'),
(13, 5, 7, 1, 1, '2025-04-14 01:22:27'),
(14, 5, 7, 0, 1, '2025-04-18 14:02:05'),
(15, 6, 7, 1, 1, '2025-04-18 15:34:40'),
(16, 6, 7, 5, 5, '2025-04-18 15:38:35'),
(17, 6, 7, 6, 7, '2025-04-18 15:41:21'),
(18, 6, 7, 9, 10, '2025-04-18 15:47:38');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `time_limit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_results`
--

CREATE TABLE `test_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `test_id` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `completed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(5, 'Nguyễn Ngọc Sinh', 'sinh@1', '$2y$10$jGZ3rwhEa.LyKbWFRp1QKeiSetWuidj/ofDqSadwnahZsIa/OoJsG', 'user', '2025-03-07 15:28:50'),
(6, 'admin', 'admin@1', '$2y$10$J1QqETbkjlQ6.5xLoB8sMu7hYcUHTBk7ifVZtSEeNvYlmnzFT6BUy', 'admin', '2025-03-07 15:34:02'),
(22, 'Ngô Mai Tâm', '1@g2', '$2y$10$adytyZodBYkuYI1e0obdG.3QO38nLY1WNjF5srIc6nSKGVNlfvHZW', 'user', '2025-04-04 16:06:36'),
(23, 'adminphu', 'admin@123', '$2y$10$wW0bjUBKlEvMgA7YKkpdhu7jnKVQdfGIiACOuUjUoNtv4l6GYMmD2', 'admin', '2025-04-18 14:17:32'),
(24, 'Testweb1', 'testwweb@1', '$2y$10$1tKPz33SvHMrG41Zmd6wP.DvjPY8z9055eyluQDE5C8793AoSwGSy', 'user', '2025-04-27 14:57:30'),
(25, 'test2', 'test2@1', '$2y$10$bDFVvOfBHbQQm1gbeNhP1eIUjOzJGt3/db3Qqrx9FHoQB95mv1YNa', 'user', '2025-04-27 15:04:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dictionary`
--
ALTER TABLE `dictionary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `sub_lessons`
--
ALTER TABLE `sub_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `sub_lesson_tests`
--
ALTER TABLE `sub_lesson_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_lesson_id` (`sub_lesson_id`);

--
-- Indexes for table `sub_lesson_test_results`
--
ALTER TABLE `sub_lesson_test_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sub_lesson_id` (`sub_lesson_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_results`
--
ALTER TABLE `test_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dictionary`
--
ALTER TABLE `dictionary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_lessons`
--
ALTER TABLE `sub_lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `sub_lesson_tests`
--
ALTER TABLE `sub_lesson_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `sub_lesson_test_results`
--
ALTER TABLE `sub_lesson_test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_results`
--
ALTER TABLE `test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_lessons`
--
ALTER TABLE `sub_lessons`
  ADD CONSTRAINT `sub_lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_lesson_tests`
--
ALTER TABLE `sub_lesson_tests`
  ADD CONSTRAINT `sub_lesson_tests_ibfk_1` FOREIGN KEY (`sub_lesson_id`) REFERENCES `sub_lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_lesson_test_results`
--
ALTER TABLE `sub_lesson_test_results`
  ADD CONSTRAINT `sub_lesson_test_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sub_lesson_test_results_ibfk_2` FOREIGN KEY (`sub_lesson_id`) REFERENCES `sub_lessons` (`id`);

--
-- Constraints for table `test_results`
--
ALTER TABLE `test_results`
  ADD CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
