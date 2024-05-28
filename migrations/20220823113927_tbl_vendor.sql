-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_vendor (
    vendor_id int PRIMARY KEY AUTO_INCREMENT,
    staff_id int NOT NULL,
    vendor_email varchar(50) NOT NULL,
    vendor_name varchar(30) NOT NULL,
    vendor_building_name varchar(20) NOT NULL,
    vendor_street varchar(20) NOT NULL,
    vendor_city varchar(20) NOT NULL,
    vendor_state varchar(20) NOT NULL,
    vendor_pincode varchar(7) NOT NULL,
    vendor_phone varchar(10) NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1,
    FOREIGN KEY (staff_id) REFERENCES tbl_staff(staff_id)
);

-- migrate:down
DROP TABLE tbl_vendor;
