


-- =========================
--  USERS TABLE (Login + Roles)
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','employee','accountant','hr') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin User
-- password = admin123
INSERT INTO users (name, email, password, role) VALUES
('King Siraj', 'admin@erp.com', '$2y$10$e1uNGq2G/5p5vI8CqvG4hONu3uKkZrDJzt01MCskjL8Qpxn0vLwru', 'admin');

-- =========================
--  EMPLOYEES (HR Module)
-- =========================
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    position VARCHAR(100),
    department VARCHAR(100),
    salary DECIMAL(10,2),
    join_date DATE,
    status ENUM('active','terminated') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
--  ATTENDANCE
-- =========================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    date DATE,
    check_in TIME,
    check_out TIME,
    status ENUM('present','absent','leave') DEFAULT 'present',
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- =========================
--  PAYROLL
-- =========================
CREATE TABLE payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    month VARCHAR(20),
    basic_salary DECIMAL(10,2),
    bonus DECIMAL(10,2),
    deductions DECIMAL(10,2),
    net_salary DECIMAL(10,2),
    payment_date DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- =========================
--  FINANCE (Invoices & Expenses)
-- =========================
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100),
    amount DECIMAL(10,2),
    status ENUM('unpaid','paid','overdue') DEFAULT 'unpaid',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_type VARCHAR(100),
    amount DECIMAL(10,2),
    expense_date DATE,
    notes TEXT
);

-- =========================
--  INVENTORY
-- =========================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150),
    stock INT,
    price DECIMAL(10,2),
    supplier VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
--  SALES / CRM
-- =========================
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    deal_name VARCHAR(150),
    amount DECIMAL(10,2),
    stage ENUM('new','negotiation','won','lost') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- =========================
--  PROJECTS
-- =========================
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(150),
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('pending','in-progress','completed') DEFAULT 'pending'
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    assigned_to INT,
    task_name VARCHAR(150),
    status ENUM('pending','in-progress','completed') DEFAULT 'pending',
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE
);



-- 3️⃣ LEAVES TABLE
CREATE TABLE IF NOT EXISTS leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

INSERT INTO leaves (employee_id, start_date, end_date, reason, status) VALUES
(2, '2025-10-05', '2025-10-07', 'Family trip', 'pending'),
(4, '2025-10-01', '2025-10-02', 'Medical', 'approved'),
(5, '2025-10-10', '2025-10-12', 'Personal', 'pending');

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,       -- stock keeping unit (unique code)
    category VARCHAR(100),
    quantity INT NOT NULL DEFAULT 0,       -- stock count
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    supplier VARCHAR(150),
    status ENUM('in_stock','low_stock','out_of_stock') DEFAULT 'in_stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO inventory (product_name, sku, category, quantity, price, supplier, status) VALUES
('Car Engine Oil', 'INV001', 'Automotive', 120, 15.50, 'ABC Suppliers', 'in_stock'),
('Brake Pads', 'INV002', 'Spare Parts', 50, 25.00, 'XYZ AutoParts', 'in_stock'),
('Air Filter', 'INV003', 'Spare Parts', 8, 10.00, 'AutoWorld', 'low_stock'),
('Battery 12V', 'INV004', 'Electrical', 0, 55.00, 'PowerMax', 'out_of_stock');
 

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(150) NOT NULL,
    deal_name VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stage ENUM('new','negotiation','won','lost') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO sales (client_name, deal_name, amount, stage) VALUES
('John Motors', 'Bulk Order - Brake Pads', 2500.00, 'won'),
('AutoFix Garage', 'Supply - Engine Oil', 1200.00, 'negotiation'),
('Speedy Repairs', 'Monthly Parts Deal', 800.00, 'new'),
('CarPoint Service', 'Battery Supply Contract', 3400.00, 'lost'),
('Metro Autos', 'Air Filters Order', 650.00, 'won'),
('Highway Service Center', 'Toolkits & Parts', 1500.00, 'negotiation'),
('City Wheels', 'Annual Supply Deal', 5000.00, 'won');

