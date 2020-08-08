<div class="Statistics">
   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 main">
      <h1 class="page-header">
         <?php echo $subtitle; ?><br/>
         <small><?php echo $description; ?><br/></small>
      </h1>
   </div>
   <div class="row">
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-2 col-lg-5">
         <div class="panel panel-primary">
            <div class="panel-heading">
               <h3 class="panel-title">Select a parameter</h3>
            </div>
            <div class="panel-body">
               <select id="selectParameter" class="form-control" style="width: 50%; margin-left:25%;" onchange="getParameter()">
                  <option value="0" selected="selected">Temperature</option>
                  <option value="1">Relative humidity</option>
                  <option value="2">Luminosity</option>
               </select>
            </div>
         </div>
         <div class="panel panel-primary">
            <div class="panel-heading">
               <h3 class="panel-title" id="titleStatsTable"></h3>
            </div>
            <div class="panel-body">
               <div class="table-responsive">
                  <table class="table table-striped table-condensed">
                     <thead>
                        <tr>
                           <th></th>
                           <th id="centerText">Board #0</th>
                           <th id="centerText">Board #1</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td id="leftText"><b>min</b></td>
                           <td id="rightText"><div id="data_min0"></div></td>
                           <td id="rightText"><div id="data_min1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>max</b></td>
                           <td id="rightText"><div id="data_max0"></div></td>
                           <td id="rightText"><div id="data_max1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>&mu;</b></td>
                           <td id="rightText"><div id="data_avg0"></div></td>
                           <td id="rightText"><div id="data_avg1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>&sigma;<sup>2</sup></b></td>
                           <td id="rightText"><div id="data_variance0"></div></td>
                           <td id="rightText"><div id="data_variance1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>&sigma;</b></td>
                           <td id="rightText"><div id="data_dev_std0"></div></td>
                           <td id="rightText"><div id="data_dev_std1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>V</b></td>
                           <td id="rightText"><div id="data_coeff_var0"></div></td>
                           <td id="rightText"><div id="data_coeff_var1"></div></td>
                        </tr>
                        <tr>
                           <td id="leftText"><b>IRQ</b></td>
                           <td id="rightText"><div id="data_irq0"></div></td>
                           <td id="rightText"><div id="data_irq1"></div></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-0 col-lg-5">
         <div class="panel panel-primary">
            <div class="panel-heading">
               <h3 class="panel-title">Box plot</h3>
            </div>
            <div class="panel-body">
               <div id="boxplot"></div>
            </div>
         </div>
      </div>
   </div>
</div>