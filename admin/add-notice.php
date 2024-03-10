<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['sturecmsaid']==0)) 
{
  header('location:logout.php');
} 
else
{
  $successAlert = false;
  $dangerAlert = false;
  $msg = "";

  try
  {
      if(isset($_POST['submit']))
      {
        $nottitle = filter_input(INPUT_POST, 'nottitle', FILTER_SANITIZE_STRING);
        $classid = filter_input(INPUT_POST, 'classid', FILTER_VALIDATE_INT);
        $notmsg = filter_input(INPUT_POST, 'notmsg', FILTER_SANITIZE_STRING);

        $selectedSections = isset($_POST['sections']) ? implode(',', $_POST['sections']) : '';

        if ($classid === false || $classid === null) 
        {
            $dangerAlert = true;
            $msg = "Invalid class! Please select a valid class from the dropdown.";
        }

        $sql = "INSERT INTO tblnotice (NoticeTitle, ClassId, SectionID, NoticeMsg) VALUES (:nottitle, :classid, :sectionid, :notmsg)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':nottitle', $nottitle, PDO::PARAM_STR);
        $query->bindParam(':classid', $classid, PDO::PARAM_INT);
        
        // Split the selected sections into an array and then implode it into a comma-separated string
        $selectedSectionsArray = explode(',', $selectedSections);
        $selectedSectionsImploded = implode(',', $selectedSectionsArray);

        $query->bindParam(':sectionid', $selectedSectionsImploded, PDO::PARAM_STR);
        $query->bindParam(':notmsg', $notmsg, PDO::PARAM_STR);
        $query->execute();

        $LastInsertId = $dbh->lastInsertId();

        if ($LastInsertId > 0) 
        {
            $successAlert = true;
            $msg = "Notice has been added successfully.";
        } 
        else 
        {
            $dangerAlert = true;
            $msg = "Something went wrong! Please try again later.";
        }
      }
  }
  catch(PDOException $e)
  {
    $dangerAlert = true;
    $msg = "Ops! An error occurred while adding a notice.";
    echo "<script>console.error('Error:---> ".$e->getMessage()."');</script>";
  }
  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
   
    <title>Student  Management System || Add Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="css/style.css" />
    
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
     <?php include_once('includes/header.php');?>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
      <?php include_once('includes/sidebar.php');?>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Add Notice </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page"> Add Notice</li>
                </ol>
              </nav>
            </div>
            <div class="row">
          
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title" style="text-align: center;">Add Notice</h4>
                    <!-- Dismissible Alert messages -->
                    <?php 
                      if ($successAlert) 
                      {
                        ?>
                        <!-- Success -->
                        <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          <?php echo $msg; ?>
                        </div>
                      <?php 
                      }
                      if($dangerAlert)
                      { 
                      ?>
                        <!-- Danger -->
                        <div id="danger-alert" class="alert alert-danger alert-dismissible" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          <?php echo $msg; ?>
                        </div>
                      <?php
                      }
                      ?>

                    <form class="forms-sample" method="post" enctype="multipart/form-data">
                      
                      <div class="form-group">
                        <label for="exampleInputName1">Notice Title</label>
                        <input type="text" name="nottitle" value="" class="form-control" required='true'>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputEmail3">Notice For</label>
                          <select name="classid" id="classid" class="form-control" required='true' onchange="loadSections()">
                              <option value="">Select Class</option>
                              <?php
                              $sql2 = "SELECT * from tblclass ";
                              $query2 = $dbh->prepare($sql2);
                              $query2->execute();
                              $result2 = $query2->fetchAll(PDO::FETCH_OBJ);

                              foreach ($result2 as $row1) {
                                  ?>
                                  <option value="<?php echo htmlentities($row1->ID); ?>"><?php echo htmlentities($row1->ClassName); ?></option>
                              <?php } ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputName1">Sections</label>
                          <select name="sections[]" id="sections" class="js-example-basic-multiple w-100" multiple required='true'>
                              <!-- Options will be dynamically loaded based on the selected class -->
                          </select>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputName1">Notice Message</label>
                        <textarea name="notmsg" value="" class="form-control" required='true'></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary mr-2" name="submit">Add</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
         <?php include_once('includes/footer.php');?>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="vendors/select2/select2.min.js"></script>
    <script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="js/off-canvas.js"></script>
    <script src="js/misc.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="js/typeahead.js"></script>
    <script src="js/select2.js"></script>
    <script src="./js/manageAlert.js"></script>
    <script>
      function loadSections() 
      {
          let classid = document.getElementById('classid').value;
          let sectionsDropdown = document.getElementById('sections');

          // Clear existing options
          sectionsDropdown.innerHTML = '';

          // Make an AJAX request with GET method
          let xhr = new XMLHttpRequest();
          xhr.open('GET', 'get_sections.php?classId=' + classid, true);
          xhr.onreadystatechange = function() {
              if (xhr.readyState == 4 && xhr.status == 200) 
              {
                  let response = JSON.parse(xhr.responseText);

                  for (let i = 0; i < response.length; i++) 
                  {
                      let option = document.createElement('option');
                      
                      option.value = response[i].ID;
                      option.text = response[i].SectionName;

                      sectionsDropdown.appendChild(option);
                  }
              }
          };
          xhr.send();
      }

    </script>

    <!-- End custom js for this page -->
  </body>
</html><?php }  ?>