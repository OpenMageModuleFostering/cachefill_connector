<?php 

/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */

$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('cfconnector/remoteproc')};
CREATE TABLE {$this->getTable('cfconnector/remoteproc')}(
  `entity_id` int(10) unsigned not null auto_increment,
  `exec_key` varchar(255) not null,
  `status` smallint(5) default null,
  `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `UNQ_CACHEFILL_REMOTEPROC_EXEC_KEY` (`exec_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CACHE FILL Remote Procedure';
");

$installer->endSetup();