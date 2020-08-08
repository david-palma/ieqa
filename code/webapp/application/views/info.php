   <script type="text/javascript">
      window.onload = function ()
      {
         initialize();
      }
   </script>

   <div class="title">
      <h1>
         <?php echo $title; ?><br/>
         <small>
            <?php echo $description; ?><br/>
         </small>
      </h1>
   </div>

   <div class="row-fluid container-folio">
      <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
         <div class="table-responsive">
            <table class="table table-condensed">
               <div class="page-header">
                  <h3>Informazioni sul progetto</h3>
               </div>
               <tbody style="text-align: left; font-size: 14px;">
                  <tr class="success">
                     <td> Progetto d'esame </td>
                     <td> Sito web BookPoint </td>
                  </tr>
                  <tr class="active">
                     <td> Autore </td>
                     <td> David Palma </td>
                  </tr>
                  <tr class="success">
                     <td> Corso </td>
                     <td> Applicazioni web </td>
                  </tr>
                  <tr class="active">
                     <td> Docente </td>
                     <td> prof L. Di Gaspero </td>
                  </tr>
                  <tr class="success">
                     <td> Anno accademico </td>
                     <td> 2014-15 </td>
                  </tr>
                  <tr class="active">
                     <td> C.d.L. </td>
                     <td> Ingegneria Elettronica </td>
                  </tr>
               </tbody>
            </table>
         </div>
            
         <div class="page-header">
            <h3>Documentazione di progetto</h3>
         </div>
         Il link porta direttamente al file in formato PDF (scritto in LaTeX) relativo al mini-report di progetto.<br>
         <a href="<?php echo base_url('doument/report.pdf'); ?>"><h4 style="text-align: center;"><span class="glyphicon glyphicon-floppy-open"></span> REPORT</h4></a>
         <div class="page-header">
            <h3>Indirizzo</h3>
         </div>
         <address>
            <strong>BookPoint, Inc.</strong><br>
            Via delle Scienze 208<br>
            33100 Udine, IT<br>
            <strong>E-mail</strong><br>
            <a href="mailto:#">palma.david@spes.uniud.it</a>
         </address>
      </div>
      
      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
         <div class="page-header">
            <h3>Dove siamo</h3>
         </div>
         <div id="map"></div>
      </div>
   </div>