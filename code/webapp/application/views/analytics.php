<div class="Analytics">
   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 main">
      <h1 class="page-header">
         <?php echo $subtitle; ?><br/>
         <small><?php echo $description; ?><br/></small>
      </h1>
   </div>

   <div class="col-xs-5 col-sm-offset-3 col-sm-3">
      <h3 class="miniTitle">Graphs</h3>
      <a href="<?php echo site_url('graphs'); ?>">
        <img src="<?php echo base_url('images/graphs.png') ?>" style="width:100%;" alt="Graphs" title="Graphs"></img>
      </a>
   </div>
   <div class="col-xs-offset-2 col-xs-5 col-sm-offset-2 col-sm-3">
      <h3 class="miniTitle">Statistics</h3>
      <a href="<?php echo site_url('statistics'); ?>">
         <img src="<?php echo base_url('images/statistics.png') ?>" style="width:100%;" alt="Statistics" title="Statistics"></img>
      </a>
   </div>
</div>