CREATE DATABASE travel_website;
USE travel_website;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tours packages table
CREATE TABLE tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    destination VARCHAR(100),
    duration INT,
    price DECIMAL(10,2),
    image_url VARCHAR(255),
    inclusions TEXT,
    exclusions TEXT,
    itinerary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    tour_id INT,
    booking_date DATE,
    travelers INT,
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tour_id) REFERENCES tours(id)
);

-- Insert sample data
INSERT INTO tours (title, description, destination, duration, price, image_url, inclusions, exclusions, itinerary) VALUES
('Goa Beach Paradise', 'Enjoy the beautiful beaches of Goa with luxury stay and water sports', 'Goa', 4, 12500.00, 'goa.jpg', 'Hotel Stay, Breakfast, Airport Transfer, Water Sports', 'Flight Tickets, Lunch, Dinner, Personal Expenses', 'Day 1: Arrival and Beach Visit|Day 2: Water Sports|Day 3: Local Sightseeing|Day 4: Departure'),
('Kashmir Heaven', 'Experience the beauty of Kashmir with houseboat stay and shikara ride', 'Kashmir', 5, 18500.00, 'kashmir.jpg', 'Hotel & Houseboat Stay, All Meals, Sightseeing', 'Flight Tickets, Personal Expenses', 'Day 1: Arrival in Srinagar|Day 2: Dal Lake Shikara|Day 3: Gulmarg|Day 4: Pahalgam|Day 5: Departure'),
('Kerala Backwaters', 'Relax in the serene backwaters of Kerala with houseboat cruise', 'Kerala', 3, 9500.00, 'kerala.jpg', 'Houseboat Stay, All Meals, Backwater Cruise', 'Flight Tickets, Personal Expenses', 'Day 1: Arrival & Houseboat|Day 2: Backwater Cruise|Day 3: Departure');