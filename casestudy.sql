CREATE TABLE staff_member(
  id INT NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  PRIMARY KEY(id)
) COMMENT 'Staff member';

CREATE TABLE leave_manager(
  id INT NOT NULL AUTO_INCREMENT,
  created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  leave_days FLOAT NOT NULL,
  reason TEXT NOT NULL,
  staff_member_id INT NOT NULL,
  leave_type_manager_id INT NOT NULL,
  PRIMARY KEY(id)
) COMMENT 'Leave manager table';

CREATE TABLE leave_type_manager(
  id INT NOT NULL AUTO_INCREMENT,
  created_on TIMESTAMP NOT NULL,
  label VARCHAR(50) NOT NULL,
  PRIMARY KEY(id)
) COMMENT 'manage manager';

ALTER TABLE leave_manager
  ADD CONSTRAINT staff_member_leave_manager
    FOREIGN KEY (staff_member_id) REFERENCES staff_member (id);

ALTER TABLE leave_manager
  ADD CONSTRAINT leave_type_manager_leave_manager
    FOREIGN KEY (leave_type_manager_id) REFERENCES leave_type_manager (id);
