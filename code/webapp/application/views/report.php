<div class="Report">
   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 main">
      <h1 class="page-header">
         <?php echo $subtitle; ?><br/>
         <small><?php echo $description; ?><br/></small>
      </h1>
   </div>
   <div class="col-xs-offset-1 col-xs-10 col-xs-offset-1 col-sm-offset-4 col-sm-6">
      <div id="report-html" class="panel panel-primary">
         <div class="panel-heading"><h3 class="text-center">Report in HTML format</h3></div>
         <div class="panel-body">
            <p>
               The report contains various informations about the system and consists of the following points:
               <ul>
                  <li>Description</li>
                  <li>Information on the boards</li>
                  <li>Network information</li>
                  <li>Statistical analysis</li>
               </ul>
            </p>
            <p class="bg-warning"><span class="glyphicon glyphicon-arrow-right"></span> <i>Note: to save the report right click and "save as".</i></p>
            <hr>
            <form method="POST" action="<?php echo site_url('generate_report'); ?>">
               <button id="makeReport" class="btn btn-default btn-lg" data-toggle="tooltip" data-placement="bottom" title="Disabled if there are too few data to make a report">Generate report</button>
            </form>
         </div>
      </div>
   </div>
   </div>
</div>