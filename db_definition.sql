
--
-- Table structure for table `b_resource`
--

CREATE TABLE IF NOT EXISTS `b_resource` (
  `resource_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `guid` varchar(4096) NOT NULL,
  `link` varchar(4096) NOT NULL,
  `description` text NOT NULL,
  `magnet` varchar(4096) NOT NULL default '',
  `pubDate` int(11) NOT NULL,
  `btih` char(40) NOT NULL default '',
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `b_resource`
--
ALTER TABLE `b_resource`
  ADD PRIMARY KEY (`resource_id`), ADD KEY `guid` (`guid`(255)), ADD KEY `link` (`link`(255));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `b_resource`
--
ALTER TABLE `b_resource`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `b_resource` ADD INDEX(`pubDate`);

ALTER TABLE `b_resource` ADD INDEX(`magnet`);

ALTER TABLE `b_resource` ADD INDEX(`btih`);
