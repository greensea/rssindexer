--
-- 表的结构 `b_resource`
--

CREATE TABLE `b_resource` (
  `resource_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `guid` varchar(4096) NOT NULL,
  `link` varchar(4096) NOT NULL,
  `description` text NOT NULL,
  `magnet` varchar(4096) NOT NULL DEFAULT '',
  `btih` char(40) NOT NULL DEFAULT '',
  `src` enum('popgo','dmhy','','') NOT NULL COMMENT '资源来源',
  `pubDate` int(11) NOT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `b_resource`
--
ALTER TABLE `b_resource`
  ADD PRIMARY KEY (`resource_id`),
  ADD UNIQUE KEY `guid` (`guid`(255)) USING BTREE,
  ADD KEY `link` (`link`(255)),
  ADD KEY `pubDate` (`pubDate`),
  ADD KEY `magnet` (`magnet`(255)),
  ADD KEY `btih` (`btih`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `b_resource`
--
ALTER TABLE `b_resource`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;
