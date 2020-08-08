<div class="Options">
   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 main">
      <h1 class="page-header">
         <?php echo $subtitle; ?><br/>
         <small><?php echo $description; ?><br/></small>
      </h1>
      <div id="message" class="container"></div> <br/>
   </div>

   <div class="col-xs-12 col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10 container">
      <table class="table-responsive table-condensed table-hover table-bordered">
         <thead>
            <tr>
               <th><h4>Option</h4></th>
               <th><h4>Description</h4></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row">
                  <h5>
                     <button type="button" class="btn btn-link" data-toggle="modal" data-target="#emptyTable">
                        Empty table
                     </button>
                  </h5>
               </th>
               <td><h5>Removes all rows from a table without logging the individual row deletions.</h5></td>
            </tr>
            <tr>
               <th scope="row">
                  <h5>
                     <button type="button" class="btn btn-link" data-toggle="modal" data-target="#editSampTime">
                        Edit sampling time
                     </button>
                  </h5>
               </th>
               <td><h5>Changes the sampling time in according to the possible values, which are: 15s (test mode), 6m, 30m, 1h, 6h.</h5></td>
            </tr>
            <tr>
               <th scope="row">
                  <h5>
                     <button type="button" class="btn btn-link" data-toggle="modal" data-target="#editThresholds">
                        Edit threshold values
                     </button>
                  </h5>
               </th>
               <td><h5>Changes the threshold values, which activate the actuators, on the selected board.</h5></td>
            </tr>
         </tbody>
      </table>

      <!-- Modal for "empty table"-->
      <div class="modal fade" id="emptyTable" tabindex="-1" role="dialog" aria-labelledby="label1">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="label1">Empty the table</h5>
            </div>
            <div class="modal-body">
               This action removes all rows from the table, but the table structure and its columns, constraints, indexes, and so on remain. <br/>
               This operation is not reversible!
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               <button id="applyEmptyDB" class="btn btn-primary" data-dismiss="modal">Apply</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal for "edit sampling time"-->
      <div class="modal fade" id="editSampTime" tabindex="-1" role="dialog" aria-labelledby="label2">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="label2">Edit sampling time</h5>
            </div>
            <div class="modal-body">
               This action modifies the parameter used by the firmware loaded on the selected base station. This operation may take a while for the change to be effected. <br/>
               Select the base station/s: <br/>
               <form role="form">
                  <div class="checkbox">
                     <label>
                        <input type="checkbox" id="b0" checked>Base station &#35;0
                     </label>
                  </div>
                  <div class="checkbox">
                     <label>
                        <input type="checkbox" id="b1" checked>Base station &#35;1
                     </label>
                  </div>
               </form>
               <br/>
               Select the sampling time rate: <br/>
               <form role="form">
                  <select class="form-control" id="samplingTime">
                     <option value="0" selected>15  seconds (default for tests)</option>
                     <option value="1">6  minutes</option>
                     <option value="2">30 minutes</option>
                     <option value="3">1  hour</option>
                     <option value="4">6  hours</option>
                  </select>
               </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" id="applySamplingTime" class="btn btn-primary" data-dismiss="modal">Apply</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal for "edit thresholds"-->
      <div class="modal fade" id="editThresholds" tabindex="-1" role="dialog" aria-labelledby="label3">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="label3">Edit threshold values</h5>
            </div>
            <div class="modal-body">
               This action modifies the threshold values used by the firmware loaded on the selected base station. This operation may take a while for the change to be effected. <br/>
               <br/><br/>
               Select the base station/s: <br/>
               <div class="col-xs-12">
                  <div class="checkbox">
                     <label>
                        <input type="checkbox" id="b2" checked>Base station &#35;0
                     </label>
                  </div>
                  <div class="checkbox">
                     <label>
                        <input type="checkbox" id="b3" checked>Base station &#35;1
                     </label>
                  </div>
               </div>
               <br/>
               Edit temperature thresholds (Celsius): <br/>
               <div class="form-horizontal">
                  <div class="form-group">
                     <div class="col-sm-2 control-label">min</div>
                     <div class="col-sm-4">
                        <select class="form-control" id="minTemp">
                           <option value="14">14</option>
                           <option value="15">15</option>
                           <option value="16" selected>16</option>
                           <option value="17">17</option>
                           <option value="18">18</option>
                           <option value="19">19</option>
                           <option value="20">20</option>
                           <option value="21">21</option>
                           <option value="22">22</option>
                           <option value="23">23</option>
                        </select>
                     </div>
                     <div class="col-sm-2 control-label">max</div>
                     <div class="col-sm-4">
                        <select class="form-control" id="maxTemp">
                           <option value="24">24</option>
                           <option value="25">25</option>
                           <option value="26" selected>26</option>
                           <option value="27">27</option>
                           <option value="28">28</option>
                           <option value="29">29</option>
                           <option value="30">30</option>
                           <option value="31">31</option>
                           <option value="32">32</option>
                           <option value="33">33</option>
                        </select>
                     </div>
                  </div>
               </div>
               <br/>
               Edit relative humidity threshold (percentage): <br/>
               <div class="form-horizontal">
                  <div class="form-group">
                     <div class="col-sm-2 control-label">max</div>
                     <div class="col-sm-4">
                        <select class="form-control" id="maxRH">
                           <option value="50">50</option>
                           <option value="55">55</option>
                           <option value="60">60</option>
                           <option value="65">65</option>
                           <option value="70" selected>70</option>
                           <option value="75">75</option>
                           <option value="80">80</option>
                           <option value="85">85</option>
                           <option value="90">90</option>
                           <option value="95">95</option>
                        </select>
                     </div>
                  </div>
               </div>
               <br/>
               Edit luminosity/brightness threshold (lux): <br/>
               <div class="form-horizontal">
                  <div class="form-group">
                     <div class="col-sm-2 control-label">min</div>
                     <div class="col-sm-4">
                        <select class="form-control" id="minLux">
                           <option value="40" >40 </option>
                           <option value="80" >80 </option>
                           <option value="120">120</option>
                           <option value="160" selected>160</option>
                           <option value="200">200</option>
                           <option value="240">240</option>
                           <option value="280">280</option>
                           <option value="320">320</option>
                           <option value="360">360</option>
                        </select>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" id="applyThresholdValues" class="btn btn-primary" data-dismiss="modal">Apply</button>
            </div>
          </div>
        </div>
      </div>
   </div>
</div>
