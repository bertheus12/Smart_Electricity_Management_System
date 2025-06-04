<?php
session_start();
if (!isset($_SESSION['admin_ID'])) {
  header("location:../index.php");
} else {
  $admin_ID = $_SESSION['admin_ID'];
}
include '../includes/db_conn.php'; // the  connection to the database

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>School bus monitoring system</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Preloader
    <div class="preloader flex-column justify-content-center align-items-center">

      <img class="img-circle bg-info" src="../images/development/loader.SVG" alt="Enlighten tech" height="50" width="50">
    </div> -->

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>


      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">


        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="includes/logout.php" role="button">
            <i class="fa fa-power-off"></i>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="index.html" class="brand-link">
        <i class="fa fa-bus  img-circle elevation-3" style="opacity: .8;"></i>
        <!--      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">-->
        <span class="brand-text font-weight-light">SBT</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <?php    // to select the admin information       
            $sql = $conn->query("SELECT * FROM admin WHERE admin_ID='$admin_ID'");
            while ($row = $sql->fetch_array()) {
              $admin_names = $row['admin_names'];
              $profile = $row['profile'];
            }
            ?>
            <!-- end for the php script -->

            <img src="../images/admin/<?php echo $profile; ?>" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block">
              <?php echo $admin_names; ?>
              <!-- to display the admin name -->
            </a>
          </div>
        </div>
       



      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-light">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM students WHERE status='1'");
                  $students_num = $sql->num_rows;
                  ?>
                  <h4 class="digital-clocks"></h4>

                  <p><?php echo date("D d-m-Y"); ?></p>
                </div>
                <div class="icon">
                  <i class="fas fa-clock"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM students WHERE status='1'");
                  $students_num = $sql->num_rows;
                  ?>
                  <h4><?php echo $students_num; ?></h4>

                  <p>Students</p>
                </div>
                <div class="icon">
                  <i class="fa fa-graduation-cap"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM parents WHERE status='1'");
                  $parent_num = $sql->num_rows;
                  ?>
                  <h4><?php echo $parent_num; ?></h4>

                  <p>Parents</p>
                </div>
                <div class="icon">
                  <i class="fa fa-users"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM drivers WHERE status='1'");
                  $driver_num = $sql->num_rows;
                  ?>
                  <h4><?php echo $driver_num; ?></h4>

                  <p>Drivers</p>
                </div>
                <div class="icon">
                  <i class="fa fa-car"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM legal_guardians WHERE status='1'");
                  $guardian_num = $sql->num_rows;
                  ?>
                  <h4><?php echo $guardian_num; ?></h4>

                  <p>Guardians</p>
                </div>
                <div class="icon">
                  <i class="fa fa-user-md"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-pink">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM bus WHERE status='1'");
                  $bus = $sql->num_rows;
                  ?>
                  <h4><?php echo $bus; ?></h4>

                  <p>School bus</p>
                </div>
                <div class="icon">
                  <i class="fa fa-bus"></i>
                </div>

              </div>

            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-primary">
                <div class="inner">
                  <?php
                  $sql = $conn->query("SELECT *  FROM data WHERE status='1'");
                  $on = $sql->num_rows;
                  ?>
                  <h4><?php echo $on; ?></h4>

                  <p>On board students</p>
                </div>
                <div class="icon">
                  <i class="fa fa-child"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-teal">
                <div class="inner">
                  <?php
                  $search_date = strtotime("today");
                  $end_date = $search_date + (60 * 60 * 24);
                  $sql = "SELECT * FROM data WHERE board_time>$search_date AND board_time<$end_date";
                  $exe = $conn->query($sql);
                  $total_b = $exe->num_rows;
                  ?>
                  <h4><?php echo $total_b; ?></h4>

                  <p>Total boarded students</p>
                </div>
                <div class="icon">
                  <i class="fas  fa-users"></i>
                </div>

              </div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->
          <!-- Main row -->
         
          <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
      <strong>Copyright &copy; 2025 </strong>
      All rights reserved.

    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="../plugins/jquery/jquery.js"></script>
  <script src="../plugins/jquery/jquery.min.js"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
  <script>
    $.widget.bridge('uibutton', $.ui.button)
  </script>
  <!-- Bootstrap 4 -->
  <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- ChartJS -->
  <script src="../plugins/chart.js/Chart.min.js"></script>
  <!-- Sparkline -->
  <script src="../plugins/sparklines/sparkline.js"></script>
  <!-- JQVMap -->
  <script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
  <script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
  <!-- jQuery Knob Chart -->
  <script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
  <!-- daterangepicker -->
  <script src="../plugins/moment/moment.min.js"></script>
  <script src="../plugins/daterangepicker/daterangepicker.js"></script>
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <!-- Summernote -->
  <script src="../plugins/summernote/summernote-bs4.min.js"></script>
  <!-- overlayScrollbars -->
  <script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../dist/js/adminlte.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="../dist/js/demo.js"></script>
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <script src="../dist/js/pages/dashboard.js"></script>
  <!-- ChartJS -->
  <script src="../../plugins/chart.js/Chart.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      clockUpdate();
      setInterval(clockUpdate, 1000);
    })

    function clockUpdate() {
      var date = new Date();

      function addZero(x) {
        if (x < 10) {
          return x = '0' + x;
        } else {
          return x;
        }
      }


      var h = addZero(date.getHours());
      var m = addZero(date.getMinutes());
      var s = addZero(date.getSeconds());

      $('.digital-clocks').text(h + ':' + m + ':' + s)
    }
  </script>


  <?php


  $d = date("Y");

  $xx = 12;
  if ($d == date('Y')) {
    $xx = date('m');
  }
  $i = 1;
  $j = 2;
  $barchart_data = '';
  $barchart_data_drivers = '';
  $barchart_data_parents = '';
  $barchart_data_guardians = '';
  $bar_values = '';
  $k = 1;
  while ($k <= $xx) {
    $start = strtotime("01-" . $i . "-" . $d);
    $end = strtotime("01-" . $j . "-" . $d);
    if ($k == 12) {
      $end = strtotime("01-01-" . ($d + 1));
    }
    $val = date("M", $start);
    // the script for the students 

    $sql = "SELECT * FROM students WHERE  date_added>='$start' AND date_added <= '$end'";
    $exe = $conn->query($sql);
    $students = $exe->num_rows;
    $barchart_data .= $students . ',';
    $bar_values .= "'" . $val . "',";
    // -------------- end of the script for the students ------

    // the script for the parents 

    $sql = "SELECT * FROM parents WHERE  date_added>='$start' AND date_added <= '$end'";
    $exe = $conn->query($sql);
    $parents = $exe->num_rows;
    $barchart_data_parents .= $parents . ',';

    // -------------- end of the script for the students ------
    // the script for the guardians  

    $sql = "SELECT * FROM legal_guardians  WHERE  date_added>='$start' AND date_added <= '$end'";
    $exe = $conn->query($sql);
    $guardians = $exe->num_rows;
    $barchart_data_guardians .= $guardians . ',';

    // -------------- end of the script for the students ------
    // the script for the drivers   

    $sql = "SELECT * FROM drivers  WHERE  date_added>='$start' AND date_added <= '$end'";
    $exe = $conn->query($sql);
    $drivers = $exe->num_rows;
    $barchart_data_drivers .= $drivers . ',';

    // -------------- end of the script for the students ------
    $i += 1;
    $j += 1;
    $k++;
  }
  ?>

  <?php
  // the script to draw the line chart for the boarded students 
  $dat = "1-" . date('m-Y'); // to initialize the month 
  $startdate = strtotime($dat);
  $enddate = strtotime("tomorrow");
  $dayz = $enddate - $startdate;
  $rounds = ceil($dayz / 60 / 60 / 24) + 1;
  $chart_data_line = '';
  $chart_data_val = '';
  $k = 1;
  while ($k <= $rounds) { // to loop until  the current date 
    $start = $startdate;
    $end = $start + (24 * 60 * 60);
    $val_line = date("d/m/Y", $start);
    $sql = "SELECT * FROM data WHERE  arrival_time>='$start' AND arrival_time <= '$end' ";
    $exe = $conn->query($sql);
    $board_total = $exe->num_rows;
    $chart_data_line .= $board_total . ',';
    $chart_data_val .= "'" . $val_line . "',";
    $startdate = $startdate + (24 * 60 * 60);
    $k++;
  }
  ?>


  <script>
    $(function() {
      /* ChartJS
       * -------
       * Here we will create a few charts using ChartJS
       */
      var barChartData = {
        labels: [<?php echo $bar_values; ?>],
        datasets: [{
            label: 'Students',
            backgroundColor: '#dc3545',
            borderColor: '#dc3545',
            pointRadius: true,

            data: [<?php echo $barchart_data; ?>]
          },
          {
            label: 'Parents',
            backgroundColor: '#6610f2',
            borderColor: '#6610f2',
            pointRadius: true,

            data: [<?php echo $barchart_data_parents; ?>]
          },
          {
            label: 'Guardians',
            backgroundColor: 'rgba(60,141,188,0.9)',
            borderColor: 'rgba(60,141,188,0.8)',
            pointRadius: true,

            data: [<?php echo $barchart_data_guardians; ?>]
          },
          {
            label: 'Drivers',
            backgroundColor: '#ffc107',
            borderColor: '#ffc107',
            pointRadius: true,

            data: [<?php echo $barchart_data_drivers; ?>]
          },


        ]
      }
      var lineChartData = {
        labels: [<?php echo $chart_data_val; ?>],
        datasets: [{
            label: 'Boarded students ',
            backgroundColor: 'rgba(60,141,188,0.9)',
            borderColor: 'rgba(60,141,188,0.8)',
            pointRadius: true,

            data: [<?php echo $chart_data_line; ?>]
          },

        ]
      }
      var ChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
          display: true
        },
        scales: {
          xAxes: [{
            gridLines: {
              display: false,
            }
          }],
          yAxes: [{
            gridLines: {
              display: true,
            }
          }]
        }
      }


    })
  </script>
</body>

</html>