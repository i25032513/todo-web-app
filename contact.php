<?php
if (!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - ToDo Student</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main h1 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .contact-section {
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            border-left: 4px solid var(--success-color);
            transition: transform 0.2s ease;
        }
        
        .contact-section:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(46, 196, 182, 0.1);
        }
        
        .contact-section h2 {
            color: var(--secondary-color);
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-section h2::before {
            content: '✉';
            background: var(--success-color);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .contact-section p {
            color: var(--text-color);
            line-height: 1.8;
            font-size: 15px;
        }
        
        .contact-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #eef2ff;
            margin-top: 15px;
        }
        
        .contact-info p {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .contact-info p:last-child {
            border-bottom: none;
        }
        
        .contact-info strong {
            color: var(--primary-color);
            font-weight: 600;
            display: inline-block;
            min-width: 130px;
        }
        
        .contact-section ul {
            list-style: none;
            padding-left: 0;
            margin-top: 15px;
        }
        
        .contact-section ul li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: var(--text-color);
            border-bottom: 1px solid #eef2ff;
        }
        
        .contact-section ul li:last-child {
            border-bottom: none;
        }
        
        .contact-section ul li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-weight: bold;
            font-size: 20px;
        }
        
        .contact-section strong {
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
            <h1>Contact Us</h1>
            
            <div class="contact-section">
                <h2>Get in Touch</h2>
                <p>We'd love to hear from you! Whether you have questions, feedback, or need support with ToDo Student, feel free to reach out to us.</p>
            </div>
            
            <div class="contact-section">
                <h2>Contact Information</h2>
                <div class="contact-info">
                    <p><strong>Email:</strong> support@todostudent.edu</p>
                    <p><strong>Phone:</strong> +60 3-1234 5678</p>
                    <p><strong>Office Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM (MYT)</p>
                </div>
            </div>
            
            <div class="contact-section">
                <h2>Feedback & Support</h2>
                <p>Your feedback is important to us! If you encounter any issues or have suggestions for improving ToDo Student, please don't hesitate to contact us. We're committed to making your task management experience as smooth as possible.</p>
            </div>
            
            <div class="contact-section">
                <h2>Technical Support</h2>
                <p>For technical issues or questions about using specific features:</p>
                <ul>
                    <li>Task management queries</li>
                    <li>Account-related issues</li>
                    <li>Feature requests and suggestions</li>
                    <li>Bug reports</li>
                </ul>
                <p>Please email us at <strong>techsupport@todostudent.edu</strong> with a detailed description of your issue, and we'll get back to you as soon as possible.</p>
            </div>
            
            <div class="contact-section">
                <h2>Academic Partnerships</h2>
                <p>Are you an educator or institution interested in partnering with us? Contact us at <strong>partnerships@todostudent.edu</strong> to discuss collaboration opportunities.</p>
            </div>
            
            <div class="contact-section">
                <h2>Address</h2>
                <p>
                    ToDo Student Development Team<br>
                    Open University Malaysia<br>
                    Jalan Tun Ismail<br>
                    50480 Kuala Lumpur, Malaysia
                </p>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
