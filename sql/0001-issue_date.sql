ALTER TABLE `publication`
ADD `issue_year` int NULL AFTER `issue_date`,
ADD `issue_month` int NULL AFTER `issue_year`;

update publication set issue_year = year(issue_date);
update publication set issue_year = null where issue_year=0;

update publication set issue_month=month(issue_date);
update publication set issue_month = null where issue_month=0;

ALTER TABLE `publication`
DROP `issue_date`;