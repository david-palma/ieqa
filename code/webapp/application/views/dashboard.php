<div class="Dashboard">
   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 main">
      <h1 class="page-header">
         <?php echo $subtitle; ?><br/>
         <small><?php echo $description; ?><br/></small>
      </h1>
   </div>
   <div class="row">
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-2 col-lg-5">
         <div class="panel panel-primary" id="status-boards">
            <div class="panel-heading"><h3 class="panel-title">Status boards</h3></div>
            <div class="panel-body">
               <div class="table-responsive">
                  <table class="table table-striped table-condensed">
                     <thead>
                        <tr>
                           <th></th>
                           <th id="centerText">Board &#35;0</th>
                           <th id="centerText">Board &#35;1</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td id="leftText">IP</td>
                           <td id="centerText">192.168.43.10</td>
                           <td id="centerText">192.168.43.20</td>
                        </tr>
                        <tr>
                           <td id="leftText">Netmask</td>
                           <td id="centerText">255.255.255.0</td>
                           <td id="centerText">255.255.255.0</td>
                        </tr>
                        <tr>
                           <td id="leftText">Network</td>
                           <td id="centerText">192.168.43.0</td>
                           <td id="centerText">192.168.43.0</td>
                        </tr>
                        <tr>
                           <td id="leftText">Status</td>
                           <td id="centerText"><div id="statusBoard0"></div></td>
                           <td id="centerText"><div id="statusBoard1"></div></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-0 col-lg-5">
         <div class="panel panel-primary" id="compliance">
            <div class="panel-heading"><h3 class="panel-title">Compliance</h3></div>
            <div class="panel-body">
               <div class="col-xxs-0 col-xs-6 col-sm-6">
                  <div id="gauge" class="gauge1"></div>
               </div>
               <div class="col-xxs-12 col-xs-6 col-sm-6">
                  <div class="gauge2">
                     <select id="selectBoard" class="form-control" onchange="getBoard()">
                       <option value="0" selected="selected">Board #0</option>
                       <option value="1">Board #1</option>
                     </select><br/>
                     <div class="table-responsive">
                        <table class="table table-striped table-condensed">
                           <tbody>
                              <tr>
                                 <td id="leftText">Temperature</td>
                                 <td id="rightText"><div id="data_Tc"></div></td>
                              </tr>
                              <tr>
                                 <td id="leftText">Relative humidity</td>
                                 <td id="rightText"><div id="data_RH"></div></td>
                              </tr>
                              <tr>
                                 <td id="leftText">Luminosity</td>
                                 <td id="rightText"><div id="data_lux"></div></td>
                              </tr>
                              <tr>
                                 <td id="leftText">Quality Index</td>
                                 <td id="rightText"><div id="data_QI"></div></td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <br/>
   <div class="row">
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-2 col-lg-5">
         <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title">Graphs</h3></div>
            <div class="panel-body">
               <select id="selectChart" class="form-control" onchange="getChart()">
                 <option value="0" selected="selected">Temperature</option>
                 <option value="1">Relative humidity</option>
                 <option value="2">Luminosity</option>
               </select><br/>
               <div id="dashboardChart"></div>
            </div>
         </div>
      </div>
      <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-3 col-md-9 col-lg-offset-0 col-lg-5">
         <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title" id="titleDataTable"></h3></div>
            <div class="panel-body">
               <table id="sensorsTable" class="display nowrap">
                  <thead>
                     <tr>
                        <th style="width: 100%;">&#35;</th>
                        <th style="width: 100%;">Time</th>
                        <th style="width: 100%;">T [&deg; C]</th>
                        <th style="width: 100%;">RH [&#37;]</th>
                        <th style="width: 100%;">Lum [lux]</th>
                     </tr>
                  </thead>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
