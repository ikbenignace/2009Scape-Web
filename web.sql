-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Gegenereerd op: 08 mei 2020 om 11:54
-- Serverversie: 5.6.45-log
-- PHP-versie: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `imellaeu_web`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `boards`
--

CREATE TABLE `boards` (
  `board_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(30) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `view_requirement` longtext,
  `post_requirement` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden geëxporteerd voor tabel `boards`
--

INSERT INTO `boards` (`board_id`, `title`, `description`, `hide`, `view_requirement`, `post_requirement`) VALUES
(1, 'News & Announcements', 'Holds news/announcements made by the Development Team. This board does not allow any non-staff members to create threads.', 0, '', 'rights:2'),
(2, 'Server & Website Updates', 'Major updates will be posted here by the staff team. For small patch notes, visit our development log.', 0, '', 'rights:2'),
(3, 'General Discussion', 'Any discussions that do not fall into the other forum categories may be posted here.', 0, NULL, NULL),
(4, 'Introduction', 'New to the community? Come introduce yourself! No quitting threads allowed.', 0, NULL, NULL),
(5, 'Reports, Bugs & Support', 'Post all bug, player, and website reports here. Don\'t worry, anything you post will be kept confidential between you and the staff team.', 0, 'self', NULL),
(6, 'Suggestions', 'All suggestions for the website/server may be posted in here. Please read the pinned topic for the rules on suggestion threads', 0, NULL, NULL),
(7, 'Vernox Guides', 'Player written guides can be put in this category. Guides will eventually end up on the wiki. Abide by sectional rules.', 0, NULL, NULL),
(8, 'Media', 'Media such as videos, graphics, and streaming channels are posted here.', 0, NULL, NULL),
(9, 'Donator Board', 'A private board used for only the donators. Only donators and staff members have access to this board.', 0, 'donator', ''),
(10, 'Staff Board', 'A private board for the staff members. Staff members must not disclose any information from this board.', 0, 'rights:2', NULL);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `forum_settings`
--

CREATE TABLE `forum_settings` (
  `key` varchar(30) NOT NULL DEFAULT '',
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden geëxporteerd voor tabel `forum_settings`
--

INSERT INTO `forum_settings` (`key`, `value`) VALUES
('announcement', 'Announcement~Fellow our players, please take the time to give us feedback and suggestions.<br><br>Your thoughts and opinions mean alot to us, it helps us create the game that <b>YOU</b> want.\r\n\r\n'),
('board_order', '1,2,10,3,4,5,6,7,8'),
('footer_modules', 'ForumStatistics'),
('sidebar_modules', 'Announcement,RecentThreads,RecentPosts,TopPoster');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `uid` int(11) NOT NULL,
  `notification` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `opened` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) UNSIGNED NOT NULL,
  `thread_id` int(11) NOT NULL,
  `board_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `poster` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastEdit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `first_post` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `recent_visitors`
--

CREATE TABLE `recent_visitors` (
  `id` int(11) UNSIGNED NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `thanks`
--

CREATE TABLE `thanks` (
  `id` int(11) UNSIGNED NOT NULL,
  `thanker` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `threads`
--

CREATE TABLE `threads` (
  `thread_id` int(11) UNSIGNED NOT NULL,
  `board_id` int(11) NOT NULL,
  `title` varchar(56) NOT NULL DEFAULT '',
  `starter_uid` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pinned` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `visitor_messages`
--

CREATE TABLE `visitor_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `content` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`board_id`);

--
-- Indexen voor tabel `forum_settings`
--
ALTER TABLE `forum_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexen voor tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexen voor tabel `recent_visitors`
--
ALTER TABLE `recent_visitors`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `thanks`
--
ALTER TABLE `thanks`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `threads`
--
ALTER TABLE `threads`
  ADD PRIMARY KEY (`thread_id`);

--
-- Indexen voor tabel `visitor_messages`
--
ALTER TABLE `visitor_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `boards`
--
ALTER TABLE `boards`
  MODIFY `board_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT voor een tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT voor een tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT voor een tabel `recent_visitors`
--
ALTER TABLE `recent_visitors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT voor een tabel `thanks`
--
ALTER TABLE `thanks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT voor een tabel `threads`
--
ALTER TABLE `threads`
  MODIFY `thread_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT voor een tabel `visitor_messages`
--
ALTER TABLE `visitor_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
