<?php
include '../Connection/conn.php';
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $title = $_POST['title'];
   $description = $_POST['description'];
   $date = $_POST['date']; 
   $image = $_FILES['image']['name'];
   $image_tmp = $_FILES['image']['tmp_name'];
   move_uploaded_file($image_tmp, "../uploaded_img/$image");

   $stmt = $conn->prepare("INSERT INTO announcements (title, description, image, date) VALUES (?, ?, ?, ?)");
   $stmt->execute([ $title, $description, $image, $date]); // ✅ INCLUDE TYPE
   header("Location: adminAnnouncements.php");
   exit;
}

?>

<!DOCTYPE html>
<html>
<head>
   <title>Add Announcement</title>
   <style>
      body {
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
         background-color: #ffffff;
         margin: 0;
         padding: 0;
      }
      .container {
         max-width: 700px;
         margin: 40px auto;
         background-color: #fff;
         border-radius: 12px;
         padding: 30px;
         box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      }
      h1 {
         text-align: center;
         margin-bottom: 30px;
      }
      label {
         display: block;
         font-weight: 600;
         margin-top: 20px;
         margin-bottom: 5px;
      }
      input[type="text"],
      input[type="file"],
      input[type="date"],
      textarea {
         width: 100%;
         padding: 10px;
         border: 1px solid #ccc;
         border-radius: 8px;
         font-size: 15px;
      }
      textarea {
         height: 100px;
         resize: vertical;
      }
      button {
         margin-top: 25px;
         width: 100%;
         padding: 12px;
         background-color: #014A7F;
         border: none;
         color: #fff;
         font-size: 16px;
         font-weight: bold;
         border-radius: 8px;
         cursor: pointer;
         transition: background-color 0.3s ease;
      }
      button:hover {
         background-color: #014A7F;
      }
      .back-link {
         display: block;
         text-align: center;
         margin-top: 20px;
         color: #014A7F;
         text-decoration: none;
         font-size: 20px;
      }
      .back-link:hover {
         text-decoration: underline;
      }
      select {
   width: 100%;
   padding: 10px;
   border: 1px solid #ccc;
   border-radius: 8px;
   font-size: 15px;
   background-color: #fff;
   appearance: none;
   -webkit-appearance: none;
   -moz-appearance: none;
   background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Cpath fill='gray' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
   background-repeat: no-repeat;
   background-position: right 10px center;
   background-size: 16px 16px;
}

   </style>
</head>
<body>

   <?php include 'adminSidebar.php' ?>
   <div class="container">
       <form method="POST" enctype="multipart/form-data">
      <h1>Add News & Announcement</h1>
         <label>Title:</label>
         <input type="text" name="title" required>

         <label>Description:</label>
         <textarea name="description" required></textarea>

         <label>Image:</label>
         <input type="file" name="image" required>

         <label>Date:</label>
         <input type="date" name="date" required>

         <button type="submit">Submit</button>
      </form>
      <a class="back-link" href="adminAnnouncements.php">← Back to Announcements</a>
   </div>
</body>
</html>
