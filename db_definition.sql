
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
  ADD KEY `btih` (`btih`,`ctime`) USING BTREE;

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
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `b_resource`
--
ALTER TABLE `b_resource`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;
