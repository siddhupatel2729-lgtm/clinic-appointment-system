CREATE DATABASE IF NOT EXISTS clinic_appointment;
USE clinic_appointment;

CREATE TABLE IF NOT EXISTS doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    date_of_birth DATE,
    gender VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Booked',
    reason VARCHAR(255),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
);

-- Optional sample data
INSERT INTO doctors (name, specialization, phone, email) VALUES
('Dr. Asha Mehta','Cardiology','9876543210','asha@clinic.com'),
('Dr. Rohan Shah','Dermatology','9876501234','rohan@clinic.com');
INSERT INTO patients (name, phone, email, date_of_birth, gender) VALUES
('Priya Patel','9123456780','priya@example.com','1994-05-12','Female'),
('Kiran Desai','9988776655','kiran@example.com','1988-11-03','Male');
