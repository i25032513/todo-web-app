<?php
if (!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - ToDo Student</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main h1 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .about-section {
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s ease;
        }
        
        .about-section:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
        }
        
        .about-section h2 {
            color: var(--secondary-color);
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .about-section h2::before {
            content: '✓';
            background: var(--primary-color);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .about-section p {
            color: var(--text-color);
            line-height: 1.8;
            font-size: 15px;
        }
        
        .about-section ul {
            list-style: none;
            padding-left: 0;
        }
        
        .about-section ul li {
            padding: 12px 0;
            padding-left: 35px;
            position: relative;
            color: var(--text-color);
            line-height: 1.7;
            border-bottom: 1px solid #eef2ff;
        }
        
        .about-section ul li:last-child {
            border-bottom: none;
        }
        
        .about-section ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-weight: bold;
            font-size: 18px;
        }
        
        .about-section strong {
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main">
            <div class="card">
            <h1>About ToDo Student</h1>
            
            <div class="about-section">
                <h2>Our Purpose</h2>
                <p>ToDo Student is a comprehensive task management web application designed specifically for university students. In today's fast-paced academic environment, students juggle multiple responsibilities including assignments, discussions, club activities, and examinations. Our application helps you stay organized and manage your time effectively.</p>
            </div>
            
            <div class="about-section">
                <h2>Key Features</h2>
                <ul>
                    <li><strong>Task Recording:</strong> Add tasks such as assignments, discussions, club activities, and examinations with detailed information including title, description, due date, and category.</li>
                    <li><strong>Task Monitoring:</strong> View all your tasks in a structured way with filtering options to sort by category, priority, or due date.</li>
                    <li><strong>Task Status Management:</strong> Mark your tasks as "On-going," "Pending," or "Completed" to track your progress.</li>
                    <li><strong>Task Archiving:</strong> Completed tasks are moved to an archive instead of being permanently deleted, allowing you to revisit them when needed.</li>
                    <li><strong>Priority Management:</strong> Organize tasks by priority levels to focus on what matters most.</li>
                    <li><strong>Calendar View:</strong> Visualize your tasks and deadlines in a calendar format.</li>
                    <li><strong>Weekly Overview:</strong> Get a quick snapshot of your week ahead.</li>
                </ul>
            </div>
            
            <div class="about-section">
                <h2>Who We Serve</h2>
                <p>This application is tailored for university students who need a reliable assistant to help them prioritize tasks, meet deadlines, and maintain a balanced academic life. Whether you're managing coursework, extracurricular activities, or exam preparation, ToDo Student is here to support your success.</p>
            </div>
            
            <div class="about-section">
                <h2>Our Mission</h2>
                <p>We aim to empower students with the tools they need to take control of their academic responsibilities, reduce stress, and achieve their goals efficiently.</p>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
