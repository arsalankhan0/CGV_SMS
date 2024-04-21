<nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="profile-image">
                  <img class="img-xs rounded-circle" src="images/faces/face8.jpg" alt="profile image">
                  <div class="dot-indicator bg-success"></div>
                </div>
                <div class="text-wrapper">
                  <?php
                  $aid= $_SESSION['sturecmsaid'];
                  $sql="SELECT * from tbladmin where ID=:aid";

                  $query = $dbh -> prepare($sql);
                  $query->bindParam(':aid',$aid,PDO::PARAM_STR);
                  $query->execute();
                  $results=$query->fetchAll(PDO::FETCH_OBJ);

                  $cnt=1;
                  if($query->rowCount() > 0)
                  {
                  foreach($results as $row)
                  {               ?>
                  <p class="profile-name"><?php  echo htmlentities($row->AdminName);?></p>
                  <p class="designation"><?php  echo htmlentities($row->Email);?></p><?php $cnt=$cnt+1;}} ?>
                </div>
              </a>
            </li>
            <li class="nav-item nav-category">
              <span class="nav-link">Dashboard</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">
                <span class="menu-title">Dashboard</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>
            
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic-role" aria-expanded="false" aria-controls="ui-basic-role">
                <span class="menu-title">Roles</span>
                <i class="icon-organization menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic-role">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-role.php">Add Role</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-roles.php">Manage Roles</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic-permission" aria-expanded="false" aria-controls="ui-basic-permission">
                <span class="menu-title">Permissions</span>
                <i class="icon-shield menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic-permission">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-permissions.php">Assign Permissions</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-permissions.php">Manage Permissions</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic-section" aria-expanded="false" aria-controls="ui-basic-section">
                <span class="menu-title">Sections</span>
                <i class="icon-notebook menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic-section">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-section.php">Add Section</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-section.php">Manage Sections</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <span class="menu-title">Class</span>
                <i class="icon-layers menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-class.php">Add Class</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-class.php">Manage Class</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic-exam" aria-expanded="false" aria-controls="ui-basic-exam">
                <span class="menu-title">Examination</span>
                <i class="icon-book-open menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic-exam">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-exam.php">Add Exam</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-exam.php">Manage Exam</a></li>
                  <li class="nav-item"> <a class="nav-link" href="view-result.php">View Results</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#promotion" aria-expanded="false" aria-controls="promotion">
                    <span class="menu-title">Promotion</span>
                    <i class="icon-badge menu-icon"></i>
                </a>
                <div class="collapse" id="promotion">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="promote-students.php">Promote Students</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-sessions" aria-expanded="false" aria-controls="ui-sessions">
                <span class="menu-title">Session Management</span>
                <i class="icon-clock menu-icon"></i>
              </a>
              <div class="collapse" id="ui-sessions">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-session.php">Create Session</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-session.php">Set Active Session</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-basic1" aria-expanded="false" aria-controls="ui-basic1">
                <span class="menu-title">Students</span>
                <i class="icon-people menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic1">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-students.php">Add Students</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-students.php">Manage Students</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-subjects" aria-expanded="false" aria-controls="ui-subjects">
                <span class="menu-title">Subjects</span>
                <i class="icon-docs menu-icon"></i>
              </a>
              <div class="collapse" id="ui-subjects">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="create-subjects.php">Create Subjects</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-subjects.php">Manage Subjects</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-curricular" aria-expanded="false" aria-controls="ui-curricular">
                <span class="menu-title">Co-Curricular</span>
                <i class="icon-book-open menu-icon"></i>
              </a>
              <div class="collapse" id="ui-curricular">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="create-curricular.php">Create Co-Curricular</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-curricular.php">Manage Co-Curricular</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#ui-employees" aria-expanded="false" aria-controls="ui-employees">
                <span class="menu-title">Employees</span>
                <i class="icon-user menu-icon"></i>
              </a>
              <div class="collapse" id="ui-employees">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-employees.php">Add Employees</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-employees.php">Manage Employees</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                <span class="menu-title">Notice</span>
                <i class="icon-doc menu-icon"></i>
              </a>
              <div class="collapse" id="auth">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-notice.php"> Add Notice </a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-notice.php"> Manage Notice </a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#resources" aria-expanded="false" aria-controls="resources">
                <span class="menu-title">Resources</span>
                <i class="icon-book-open menu-icon"></i>
              </a>
              <div class="collapse" id="resources">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-syllabus.php">Add Syllabus</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-syllabus.php">Manage Syllabus</a></li>
                  <li class="nav-item"> <a class="nav-link" href="add notes.php">Add Notes</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-notes.php">Manage Notes</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth1" aria-expanded="false" aria-controls="auth">
                <span class="menu-title">Public Notice</span>
                <i class="icon-doc menu-icon"></i>
              </a>
              <div class="collapse" id="auth1">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="add-public-notice.php"> Add Public Notice </a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage-public-notice.php"> Manage Public Notice </a></li>
                </ul>
              </div>
              <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#auth2" aria-expanded="false" aria-controls="auth">
                <span class="menu-title">Pages</span>
                <i class="icon-doc menu-icon"></i>
              </a>
              <div class="collapse" id="auth2">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="about-us.php"> About Us </a></li>
                  <li class="nav-item"> <a class="nav-link" href="contact-us.php"> Contact Us </a></li>
                  <li class="nav-item"> <a class="nav-link" href="gallery.php"> Gallery </a></li>
                </ul>
              </div>
            </li>
              <li class="nav-item">
              <a class="nav-link" href="between-dates-reports.php">
                <span class="menu-title">Reports</span>
                <i class="icon-notebook menu-icon"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="search.php">
                <span class="menu-title">Search</span>
                <i class="icon-magnifier menu-icon"></i>
              </a>
            </li>
            </li>
          </ul>
        </nav>