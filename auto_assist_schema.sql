-- Before running this schema, make sure to create and use your database:
-- CREATE DATABASE IF NOT EXISTS autoassist_db;
-- USE autoassist_db;

CREATE TABLE `user` (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE mechanic (
    mech_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    locality VARCHAR(100) NOT NULL,
    avg_rating DECIMAL(3,2) DEFAULT 0.0 CHECK (avg_rating >= 0 AND avg_rating <= 5),
    password VARCHAR(255) NOT NULL
);

CREATE TABLE userCar (
    car_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_type VARCHAR(50) NOT NULL,
    number_plate VARCHAR(20) UNIQUE NOT NULL,
    car_year INT NOT NULL,
    car_colour VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE
);

CREATE TABLE service (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mech_id INT,
    car_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    time_served DATETIME DEFAULT CURRENT_TIMESTAMP,
    locality VARCHAR(100) NOT NULL,
    status VARCHAR(30) DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT NULL CHECK (rating >= 0 AND rating <= 5),
    FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE,
    FOREIGN KEY (mech_id) REFERENCES mechanic(mech_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES userCar(car_id) ON DELETE CASCADE
);

CREATE TABLE payment (
    pay_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mech_id INT NOT NULL,
    service_id INT NOT NULL,
    pay_method VARCHAR(50) NOT NULL,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE,
    FOREIGN KEY (mech_id) REFERENCES mechanic(mech_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE
);

CREATE TABLE withdrawal (
    withdrawal_id INT PRIMARY KEY AUTO_INCREMENT,
    mech_id INT NOT NULL,
    withdrawal_method VARCHAR(50) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    withdrawal_amt DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (mech_id) REFERENCES mechanic(mech_id) ON DELETE CASCADE
);

CREATE TABLE balance (
    mech_id INT NOT NULL,
    date_update DATETIME DEFAULT CURRENT_TIMESTAMP,
    balance DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (mech_id, date_update),
    FOREIGN KEY (mech_id) REFERENCES mechanic(mech_id) ON DELETE CASCADE
);

CREATE TABLE admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);