SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `pubconf`.`publication` 
ADD CONSTRAINT `publication_journal_id_FK`
  FOREIGN KEY (`journal_id`)
  REFERENCES `pubconf`.`journal` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `publication_publisher_id_FK`
  FOREIGN KEY (`publisher_id`)
  REFERENCES `pubconf`.`publisher` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `publication_conference_year_id_FK`
  FOREIGN KEY (`conference_year_id`)
  REFERENCES `pubconf`.`conference_year` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS=1;
