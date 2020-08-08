<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8"></meta>
   <meta http-equiv="X-UA-Compatible" content="IE=edge"></meta>
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no"></meta>
   <meta name="author" content="David Palma"></meta>

   <title>I.E.Q.A. | <?php echo $title; ?></title>

   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="<?php echo base_url('assets/css/bootstrap.min.css') ?>"></link>
   <link rel="stylesheet" href="<?php echo base_url('assets/css/bootstrap-theme.min.css') ?>"></link>
   <link rel="stylesheet" href="<?php echo base_url('assets/css/dataTables.bootstrap.min.css') ?>"></link>
   <link rel="stylesheet" href="<?php echo base_url('assets/css/jquery.dataTables.min.css') ?>"></link>
   <link rel="stylesheet" href="<?php echo base_url('assets/css/ieqa.css') ?>"></link>
   <!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script> -->

</head>
<body>
   <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
         <!-- Brand and toggle get grouped for better mobile display -->
         <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
           </button>
            <a class="navbar-brand" href="<?php echo base_url(); ?>" style="margin-top:-24px;">
               <h3><b> I.E.Q.A.</b></h3>
               <!-- <span img alt="I.E.Q.A." src="<?php echo base_url('images/logo.png') ?>" style="height: 42px; margin-top: 0px;">-->
           </a>
         </div>

         <!-- Collect the nav links for toggling -->
         <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
               <li><a href="<?php echo site_url('dashboard'); ?>"><span class="glyphicon glyphicon-dashboard"></span><b> Dashboard</b></a></li>
               <li><a href="<?php echo site_url('analytics'); ?>"><span class="glyphicon glyphicon-stats"></span><b> Analytics</b></a></li>
               <li><a href="<?php echo site_url('report');    ?>"><span class="glyphicon glyphicon-list-alt"></span><b> Report</b></a></li>
               <li><a href="<?php echo site_url('options');   ?>"><span class="glyphicon glyphicon-cog"></span><b> Options</b></a></li>
            </ul>
         </div>
      </div>
   </nav>

   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-3 col-md-2 sidebar">
            <ul class="nav nav-sidebar">

               <li class="active"><a href="<?php echo site_url('dashboard'); ?>"><span class="glyphicon glyphicon-dashboard"></span><b> Dashboard</b><span class="sr-only">(current)</span></a></li>

               <br>

               <li class="nav-header"><a href="#" data-toggle="collapse" data-target="#menu1"><span class="glyphicon glyphicon-stats"></span><b> Analytics</b><span class="glyphicon glyphicon-chevron-right"></span></a>
                  <ul class="nav nav-stacked collapse in" id="menu1">
                     <li><a href="<?php echo site_url('graphs'); ?>">Graphs</a></li>
                     <li><a href="<?php echo site_url('statistics'); ?>">Statistics</a></li>
                  </ul>
               </li>

               <br>

               <li class="nav-header"><a href="<?php echo site_url('report'); ?>" data-toggle="collapse" data-target="#menu2"><span class="glyphicon glyphicon-list-alt"></span><b> Report</b></a>
               </li>

               <br>

               <li class="nav-header"><a href="<?php echo site_url('options'); ?>" data-toggle="collapse" data-target="#menu3"><span class="glyphicon glyphicon-cog"></span><b> Options</b></a>
               </li>
            </ul>
         </div>