--
-- Database: `rssindexer`
--

-- --------------------------------------------------------

--
-- Table structure for table `b_dht_log`
--

CREATE TABLE `b_dht_log` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `node_id` binary(20) NOT NULL,
  `btih` binary(20) NOT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `b_download_log`
--

CREATE TABLE `b_download_log` (
  `log_id` bigint(20) NOT NULL,
  `btih` binary(20) NOT NULL,
  `ctime` int(11) NOT NULL,
  `ip` varchar(15) COLLATE utf8_general_ci NOT NULL,
  `useragent` varchar(4096) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='种子下载记录表';

-- --------------------------------------------------------

--
-- Table structure for table `b_keyword_log`
--

CREATE TABLE `b_keyword_log` (
  `log_id` bigint(20) NOT NULL,
  `ip` varchar(15) COLLATE utf8_general_ci NOT NULL,
  `kw` varchar(256) COLLATE utf8_general_ci NOT NULL,
  `ctime` int(11) NOT NULL,
  `useragent` varchar(4096) COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='记录用户搜索的关键词';

-- --------------------------------------------------------

--
-- Table structure for table `b_keyword_popularity`
--

CREATE TABLE `b_keyword_popularity` (
  `popularity_id` int(11) NOT NULL,
  `kw` varchar(256) COLLATE utf8_general_ci NOT NULL,
  `popularity` double NOT NULL,
  `pmtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='关键词热度表';

-- --------------------------------------------------------

--
-- Table structure for table `b_resource`
--

CREATE TABLE `b_resource` (
  `resource_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `guid` varchar(4096) NOT NULL,
  `link` varchar(4096) NOT NULL,
  `description` text NOT NULL,
  `magnet` varchar(4096) NOT NULL DEFAULT '',
  `btih` char(40) NOT NULL DEFAULT '',
  `src` enum('popgo','dmhy','','') NOT NULL DEFAULT '' COMMENT '资源来源',
  `pubDate` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `popularity` float NOT NULL DEFAULT '-1' COMMENT '热门程度缓存',
  `pmtime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `b_dht_log`
--
ALTER TABLE `b_dht_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `btih` (`btih`,`ctime`) USING BTREE,
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `b_download_log`
--
ALTER TABLE `b_download_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `btih` (`btih`,`ctime`) USING BTREE;

--
-- Indexes for table `b_keyword_log`
--
ALTER TABLE `b_keyword_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `kw` (`kw`(255));

--
-- Indexes for table `b_keyword_popularity`
--
ALTER TABLE `b_keyword_popularity`
  ADD PRIMARY KEY (`popularity_id`),
  ADD UNIQUE `kw` (`kw`(255)) USING HASH,
  ADD KEY `popularity` (`popularity`,`pmtime`) USING BTREE;

--
-- Indexes for table `b_resource`
--
ALTER TABLE `b_resource`
  ADD PRIMARY KEY (`resource_id`),
  ADD UNIQUE KEY `btih` (`btih`) USING BTREE,
  ADD KEY `guid` (`guid`(255)),
  ADD KEY `link` (`link`(255)),
  ADD KEY `magnet` (`magnet`(255)),
  ADD KEY `pubDate` (`pubDate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `b_dht_log`
--
ALTER TABLE `b_dht_log`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `b_download_log`
--
ALTER TABLE `b_download_log`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `b_keyword_log`
--
ALTER TABLE `b_keyword_log`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `b_keyword_popularity`
--
ALTER TABLE `b_keyword_popularity`
  MODIFY `popularity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `b_resource`
--
ALTER TABLE `b_resource`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
