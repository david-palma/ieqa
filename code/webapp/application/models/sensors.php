<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sensors extends CI_Model
{
   var $id        = '';
   var $timestamp = '';   // RTC of the boards
   var $s0        = '';   // Temperature
   var $s1        = '';   // Relative humidity
   var $s2        = '';   // Luminosity/Brightness
   var $board     = '';   // Board id
   var $a, $b, $c, $d, $e, $f;

   function __construct()
   {
      parent::__construct();

      // Load database
      $this->load->database();
   }

	// 0 if database is empty or doesn't exist
   function is_not_empty($table)
   {
      if($this->db->table_exists($table))
			$query = $this->db->get($table);

      return($query->num_rows() > 0);
   }

   // Get all the informations in the database about the sensors
   function get_all()
   {
      $query = $this->db->get('sensors');
      return $query->result();
   }

   /** Get all the informations in the database about the sensors(table view) for a specific board
   *   e.g. ["id": x1, "timestamp": t1, "s0": a1, "s1": b1, "s2": c1],
   *        ...,
   *        ["id": xn, "timestamp": tn, "s0": an, "s1": bn, "s2": cn]
   */
   function get_json_table($b)
   {
      $query = $this->db->get_where('sensors', array('board' => $b));

      foreach($query->result() as $row)
      {
         $ret[] = ['id'        => $row->id,
                   'timestamp' => date('Y/m/d - H:i:s', $row->timestamp),
                   's0'        => $row->s0,
                   's1'        => $row->s1,
                   's2'        => $row->s2
                  ];
      }

      return json_encode($ret, JSON_NUMERIC_CHECK);
   }

   /** Get all the informations in the database about the sensors(graphs view)
   *   e.g. "xData": [x1,...,xn]
   *        "datasets": {["name":"name", "data":"data", "unit":"unit"],
   *                     ...,
   *                     ["name":"name", "data":"data", "unit":"unit"]}
   */
   function get_json_graphs($b)
   {
      $query = $this->db->get_where('sensors', array('board' => $b));

      foreach($query->result() as $row)
      {
         $t[]  = $row->timestamp;
         $s0[] = $row->s0;
         $s1[] = $row->s1;
         $s2[] = $row->s2;
      }

      $ret = array('xData' => $t,
                   'datasets' => array(array('name' => 'Temperature',       'data' => $s0, 'unit' => '°C'),
                                       array('name' => 'Relative humidity', 'data' => $s1, 'unit' => '%'),
                                       array('name' => 'Luminosity',        'data' => $s2, 'unit' => 'Lux')));

      return json_encode($ret, JSON_NUMERIC_CHECK);
   }

   /** Get statistics from the database for a specific board(statistics view)
   *   e.g. "table":   [["key":"val", "key":"val", ..., "key":"val"],
   *                    ...,
   *                    ["key":"val", "key":"val", ..., "key":"val"]],
   *        "boxplot": [["key":"val", "key":"val", ..., "key":"val"],
   *                    ...,
   *                    ["key":"val", "key":"val", ..., "key":"val"]]
   */
   function get_json_statistics($par)
   {
      // Get values from board #0
      $query1 = $this->db->get_where('sensors', array('board' => '0'));
      // Samples
      $N1 = $query1->num_rows();

      if($N1 > 4)
      {
         foreach($query1->result() as $row)
         {
            $a[] = $row->s0;  // Temperature
            $b[] = $row->s1;  // Relative humidity
            $c[] = $row->s2;  // Luminosity
         }

         switch($par)
         {
            case 0:  // Temperature
               $b0 = $a;
               break;

            case 1:  // Relative humidity
               $b0 = $b;
               break;

            case 2:  // Luminosity
               $b0 = $c;
               break;
         }

         $min[0] = min($b0);             // Minimum
         $max[0] = max($b0);             // Maximum
         $avg[0] = array_sum($b0)/$N1;   // Mean
         $var[0] = 0;                    // Variance

         for($i = 0; $i < $N1; $i++)
         {
            $x = pow(($b0[$i] - $avg[0]),2);
            $var[0] += $x/($N1-1);
         }

         $dev_std[0]   = sqrt($var[0]);  // Standard deviation
         $coeff_var[0] = ($avg[0] != 0) ? 100 * ($dev_std[0]/$avg[0]) : 0;  // Coefficient of variation

         // IRQ
         sort($b0);
         $IRQ[0] = abs($b0[floor(0.75 * ($N1 + 1))] - $b0[floor(0.25 * ($N1 + 1))]);

         /* Code for box plot */
         // Minima
         $min2[0] = min($a);
         $min2[1] = min($b);
         $min2[2] = min($c);

         // Maxima
         $max2[0] = max($a);
         $max2[1] = max($b);
         $max2[2] = max($c);

         $i0[0] = floor(0.25 * ($N1+1));
         $i1[0] = floor(0.50 * ($N1+1));
         $i2[0] = floor(0.75 * ($N1+1));

         sort($a);
         sort($b);
         sort($c);

         // First quartile, median and third quartile
         $quartile1[0] = $a[$i0[0]];
         $quartile1[1] = $b[$i0[0]];
         $quartile1[2] = $c[$i0[0]];

         $quartile2[0] = $a[$i1[0]];
         $quartile2[1] = $b[$i1[0]];
         $quartile2[2] = $c[$i1[0]];

         $quartile3[0] = $a[$i2[0]];
         $quartile3[1] = $b[$i2[0]];
         $quartile3[2] = $c[$i2[0]];
      }
      else
      {
         $a[] = $b[] = $c[] = 0;
         $min[0]       = 0;  // Minimum
         $max[0]       = -1; // Maximum
         $avg[0]       = 0;  // Mean
         $var[0]       = 0;  // Variance
         $dev_std[0]   = 0;  // Standard deviation
         $coeff_var[0] = 0;  // Coefficient of variance
         $IRQ[0]       = 0;  // IRQ

         /* Code for box plot */
         $min2[0] = $min2[1] = $min2[2] = 0;
         $max2[0] = $max2[1] = $max2[2] = 0;
         $quartile1[0] = $quartile1[1] = $quartile1[2] = 0;
         $quartile2[0] = $quartile2[1] = $quartile2[2] = 0;
         $quartile3[0] = $quartile3[1] = $quartile3[2] = 0;
      }

      // Get values from board #1
      $query2 = $this->db->get_where('sensors', array('board' => '1'));
      // Samples
      $N2 = $query2->num_rows();

      if($N2 > 4)
      {
         foreach($query2->result() as $row)
         {
            $d[] = $row->s0;  // Temperature
            $e[] = $row->s1;  // Relative humidity
            $f[] = $row->s2;  // Luminosity
         }

         switch($par)
         {
            case 0:  // Temperature
               $b1 = $d;
               break;

            case 1:  // Relative humidity
               $b1 = $e;
               break;

            case 2:  // Luminosity
               $b1 = $f;
               break;
         }

         $min[1] = min($b1);             // Minimum
         $max[1] = max($b1);             // Maximum
         $avg[1] = array_sum($b1)/$N2;   // Mean
         $var[1] = 0;                    // Variance

         for($i = 0; $i < $N2; $i++)
         {
            $y = pow(($b1[$i] - $avg[1]),2);
            $var[1] += $y/($N2-1);
         }

         $dev_std[1]   = sqrt($var[1]);  // Standard deviation
         $coeff_var[1] = ($avg[1] != 0) ? 100 * ($dev_std[1]/$avg[1]) : 0;  // Coefficient of variance

         // IRQ
         sort($b1);
         $IRQ[1] = abs($b1[floor(0.75 * ($N2 + 1))] - $b1[floor(0.25 * ($N2 + 1))]);

         /* Code for box plot */
         // Minima
         $min2[3] = min($d);
         $min2[4] = min($e);
         $min2[5] = min($f);

         // Maxima
         $max2[3] = max($d);
         $max2[4] = max($e);
         $max2[5] = max($f);

         $i0[1] = floor(0.25 * ($N2+1));
         $i1[1] = floor(0.50 * ($N2+1));
         $i2[1] = floor(0.75 * ($N2+1));

         sort($d);
         sort($e);
         sort($f);

         // First quartile, median and third quartile
         $quartile1[3] = $d[$i0[1]];
         $quartile1[4] = $e[$i0[1]];
         $quartile1[5] = $f[$i0[1]];

         $quartile2[3] = $d[$i1[1]];
         $quartile2[4] = $e[$i1[1]];
         $quartile2[5] = $f[$i1[1]];

         $quartile3[3] = $d[$i2[1]];
         $quartile3[4] = $e[$i2[1]];
         $quartile3[5] = $f[$i2[1]];
      }
      else
      {
         $d[] = $e[] = $f[] = 0;
         $min[1]       = 0;  // Minimum
         $max[1]       = -1; // Maximum
         $avg[1]       = 0;  // Mean
         $var[1]       = 0;  // Variance
         $dev_std[1]   = 0;  // Standard deviation
         $coeff_var[1] = 0;  // Coefficient of variance
         $IRQ[1]       = 0;  // IRQ

         /* Code for box plot */
         $min2[3] = $min2[4] = $min2[5] = 0;
         $max2[3] = $max2[4] = $max2[5] = 0;
         $quartile1[3] = $quartile1[4] = $quartile1[5] = 0;
         $quartile2[3] = $quartile2[4] = $quartile2[5] = 0;
         $quartile3[3] = $quartile3[4] = $quartile3[5] = 0;
      }

      $ret = array
            (
               'table'   => array
                           (
                              'min'       => array($min[0], $min[1]),
                              'max'       => array($max[0], $max[1]),
                              'avg'       => array($avg[0], $avg[1]),
                              'variance'  => array($var[0], $var[1]),
                              'dev_std'   => array($dev_std[0], $dev_std[1]),
                              'coeff_var' => array($coeff_var[0], $coeff_var[1]),
                              'irq'       => array($IRQ[0], $IRQ[1])
                           ),
               'boxplot' => array
                           (
                              array // Temperature
                              (
                                 array($min2[0], $quartile1[0], $quartile2[0], $quartile3[0], $max2[0]),
                                 array($min2[3], $quartile1[3], $quartile2[3], $quartile3[3], $max2[3])
                              ),
                              array // Relative humidity
                              (
                                 array($min2[1], $quartile1[1], $quartile2[1], $quartile3[1], $max2[1]),
                                 array($min2[4], $quartile1[4], $quartile2[4], $quartile3[4], $max2[4])
                              ),
                              array // Luminosity
                              (
                                 array($min2[2], $quartile1[2], $quartile2[2], $quartile3[2], $max2[2]),
                                 array($min2[5], $quartile1[5], $quartile2[5], $quartile3[5], $max2[5])
                              )
                           )
            );

      return json_encode($ret, JSON_NUMERIC_CHECK);
   }

   /* Update the sampling time into the database for the selected board */
   function update_samp_time()
   {
      $db        = 'thresholds';
      $ret       = 0;
      $board_0   = $this->input->POST('b0');
      $board_1   = $this->input->POST('b1');
      $samp_time = $this->input->POST('samp_time');

      switch($samp_time)
      {
         case 0:  // 15 seconds
            $data = array ( 't4' => 15000 );
            break;
         case 1:  // 6 minutes
            $data = array ( 't4' => 360000 );
            break;
         case 2:  // 30 minutes
            $data = array ( 't4' => 1800000 );
            break;
         case 3:  // 1 hour
            $data = array ( 't4' => 3600000 );
            break;
         case 4:  // 6 hours
            $data = array ( 't4' => 21600000 );
            break;
         default: // 15 seconds
            $data = array ( 't4' => 15000 );
      }

      if($board_0)
      {
         $this->db->where('board', 0);
         $this->db->update($db, $data);
         $ret = 1;
      }
         
      if($board_1)
      {
         $this->db->where('board', 1);
         $this->db->update($db, $data);
         $ret = 1;
      }

      return $ret;
   }

   /* Update the threshold values into the database for the selected board */
   function update_threshold_values()
   {
      $db      = 'thresholds';
      $ret     = 0;
      $board_0 = $this->input->POST('b0');
      $board_1 = $this->input->POST('b1');
      $t0      = $this->input->POST('t0');
      $t1      = $this->input->POST('t1');
      $t2      = $this->input->POST('t2');
      $t3      = $this->input->POST('t3');
      $data    = array ( 't0' => $t0, 't1' => $t1, 't2' => $t2, 't3' => $t3 );

      if($board_0)
      {
         $this->db->where('board', 0);
         $this->db->update($db, $data);
         $ret = 1;
      }
         
      if($board_1)
      {
         $this->db->where('board', 1);
         $this->db->update($db, $data);
         $ret = 1;
      }

      return $ret;
   }

   /* Insert data posted by the boards into the database */
   function insert_data_into_db()
   {
      $db        = 'sensors';
      $temp      = $this->input->POST('s0');
      $rh        = $this->input->POST('s1');
      $lux       = $this->input->POST('s2');
      $board     = $this->input->POST('board');
      $date_dev  = $this->input->POST('time');

      $time      = strptime($date_dev, '%FT%T');
      $timestamp = mktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'], $time['tm_mon']+1, $time['tm_mday'], $time['tm_year']+1900);

      $data = array
            (
                 'id'        => NULL,
                 'timestamp' => $timestamp,
                 's0'        => $temp,
                 's1'        => $rh,
                 's2'        => $lux,
                 'board'     => $board
             );
      $this->db->insert($db, $data);
   }

   function get_thresholds($b)
   {
      $default = array
               (
                  'board' =>    $b,
                  't0'    =>    18, // default threshold for min. temperature
                  't1'    =>    27, // default threshold for max. temperature
                  't2'    =>    70, // default threshold for relative humidity
                  't3'    =>   100, // default threshold for luminosity/brightness
                  't4'    => 60000  // default threshold for sampling time
               );

      $this->db->where('board', $b);
      $query = $this->db->get('thresholds');

      if($query->num_rows() == 1)
      {
         $row = $query->row();
         return json_encode($row, JSON_NUMERIC_CHECK);
      }
      else
         return json_encode($default, JSON_NUMERIC_CHECK);
   }

   /* Make report in HTML format */
   function make_report($temp, $rh, $lux)
   {
      for($i = 0; $i < 2; $i++)
      {
         $temp['table']['min'][$i]       = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['min'][$i],2);
         $temp['table']['max'][$i]       = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['max'][$i],2);
         $temp['table']['avg'][$i]       = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['avg'][$i],2);
         $temp['table']['variance'][$i]  = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['variance'][$i],2);
         $temp['table']['dev_std'][$i]   = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['dev_std'][$i],2);
         $temp['table']['coeff_var'][$i] = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['coeff_var'][$i],2);
         $temp['table']['irq'][$i]       = ($temp['table']['min'][$i] > $temp['table']['max'][$i]) ? '--' : round($temp['table']['irq'][$i],2);

         $rh['table']['min'][$i]         = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['min'][$i],2);
         $rh['table']['max'][$i]         = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['max'][$i],2);
         $rh['table']['avg'][$i]         = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['avg'][$i],2);
         $rh['table']['variance'][$i]    = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['variance'][$i],2);
         $rh['table']['dev_std'][$i]     = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['dev_std'][$i],2);
         $rh['table']['coeff_var'][$i]   = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['coeff_var'][$i],2);
         $rh['table']['irq'][$i]         = ($rh['table']['min'][$i] > $rh['table']['max'][$i]) ? '--' : round($rh['table']['irq'][$i],2);

         $lux['table']['min'][$i]        = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['min'][$i],2);
         $lux['table']['max'][$i]        = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['max'][$i],2);
         $lux['table']['avg'][$i]        = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['avg'][$i],2);
         $lux['table']['variance'][$i]   = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['variance'][$i],2);
         $lux['table']['dev_std'][$i]    = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['dev_std'][$i],2);
         $lux['table']['coeff_var'][$i]  = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['coeff_var'][$i],2);
         $lux['table']['irq'][$i]        = ($lux['table']['min'][$i] > $lux['table']['max'][$i]) ? '--' : round($lux['table']['irq'][$i],2);
      }

      $date = date('d/m/Y h:i:s a', time());
      $css1 = '<style type="text/css">' . file_get_contents(base_url('assets/css/bootstrap.min.css')) . '</style>';
      $css2 = '<style type="text/css">'
             .'   html { margin: 0 3% 0 3%; }'
             .'   body { color: #333; background-color: #F8F8EE; }'
             .'   .table > thead > tr > th,.table > tbody > tr > th,.table > tfoot > tr > th,.table > thead > tr > td,.table > tbody > tr > td,.table > tfoot > tr > td { padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #DDD; }'
             .'   .table > thead > tr > th { vertical-align: bottom; border-bottom: 2px solid #DDD; }'
             .'   .table > tbody > tr > td + td { border-left: 1px solid #DDD; }'
             .'   .table > tbody > tr:last-child > td { border-bottom: 2px solid #DDD; }'
             .'   .table > caption + thead > tr:first-child > th,.table > colgroup + thead > tr:first-child > th,.table > thead:first-child > tr:first-child > th,.table > caption + thead > tr:first-child > td,.table > colgroup + thead > tr:first-child > td,.table > thead:first-child > tr:first-child > td { border-top: 0; }'
             .'   .table > tbody + tbody { border-top: 2px solid #DDD; }'
             .'   .table-striped > tbody > tr:nth-of-type(odd)  { background-color: #F9F9F9; }'
             .'   .table-striped > tbody > tr:nth-of-type(even)  { background-color: #F5F5F5; }'
             .'   #boxplot-temp, #boxplot-rh, #boxplot-lux { margin-bottom: 3%; }'
             .'</style>';
      $js1 = '<script type=\'text/javascript\'>' . file_get_contents(base_url('assets/js/jquery-2.1.4.min.js')) . '</script>';
      $js2 = '<script type=\'text/javascript\'>' . file_get_contents(base_url('assets/js/highcharts.js')) . '</script>';
      $js3 = '<script type=\'text/javascript\'>' . file_get_contents(base_url('assets/js/highcharts-more.js')) . '</script>';
      $js_charts = '<script type=\'text/javascript\'>
                     $(function()
                     {
                        $(\'#boxplot-temp\').highcharts(
                        {
                           chart: { type: \'boxplot\', borderColor: \'#DDD\', borderWidth: 2, spacingTop: 15, spacingBottom: 10 },
                           title: { text: \'Temperature\' },
                           credits: { enabled: false },
                           exporting: { enabled: false },
                           legend: { enabled: false },
                           tooltip: { enabled: false },
                           plotOptions: { boxplot: { fillColor:\'#F0F0E0\',lineWidth:2,medianColor:\'#0C5DA5\',medianWidth:3,stemColor:\'#A63400\',stemDashStyle:\'dot\',stemWidth:1,whiskerColor:\'#3D9200\',whiskerLength:\'20%\',whiskerWidth:3 } },
                           xAxis: { categories: [\'board #0\', \'board #1\'] },
                           yAxis: { title: { text: \'Temperature [Celsius]\' }, labels: { format: \'{value}\' } },
                           series:
                           [{
                              data: [['.$temp['boxplot'][0][0][0].','.$temp['boxplot'][0][0][1].','.$temp['boxplot'][0][0][2].','.$temp['boxplot'][0][0][3].','.$temp['boxplot'][0][0][4].'],
                                     ['.$temp['boxplot'][0][1][0].','.$temp['boxplot'][0][1][1].','.$temp['boxplot'][0][1][2].','.$temp['boxplot'][0][1][3].','.$temp['boxplot'][0][1][4].']]
                           }]
                        });

                        $(\'#boxplot-rh\').highcharts(
                        {
                           chart: { type: \'boxplot\', borderColor: \'#DDD\', borderWidth: 2, spacingTop: 15, spacingBottom: 10 },
                           title: { text: \'Relative humidity\' },
                           credits: { enabled: false },
                           exporting: { enabled: false },
                           legend: { enabled: false },
                           tooltip: { enabled: false },
                           plotOptions: { boxplot: { fillColor:\'#F0F0E0\',lineWidth:2,medianColor:\'#0C5DA5\',medianWidth:3,stemColor:\'#A63400\',stemDashStyle:\'dot\',stemWidth:1,whiskerColor:\'#3D9200\',whiskerLength:\'20%\',whiskerWidth:3 } },
                           xAxis: { categories: [\'board #0\', \'board #1\'] },
                           yAxis: { title: { text: \'Relative humidity [%]\' }, labels: { format: \'{value}\' } },
                           series:
                           [{
                              data: [['.$rh['boxplot'][1][0][0].','.$rh['boxplot'][1][0][1].','.$rh['boxplot'][1][0][2].','.$rh['boxplot'][1][0][3].','.$rh['boxplot'][1][0][4].'],
                                     ['.$rh['boxplot'][1][1][0].','.$rh['boxplot'][1][1][1].','.$rh['boxplot'][1][1][2].','.$rh['boxplot'][1][1][3].','.$rh['boxplot'][1][1][4].']]
                           }]
                        });

                        $(\'#boxplot-lux\').highcharts(
                        {
                           chart: { type: \'boxplot\', borderColor: \'#DDD\', borderWidth: 2, spacingTop: 15, spacingBottom: 10 },
                           title: { text: \'Luminosity/Brightness\' },
                           credits: { enabled: false },
                           exporting: { enabled: false },
                           legend: { enabled: false },
                           tooltip: { enabled: false },
                           plotOptions: { boxplot: { fillColor:\'#F0F0E0\',lineWidth:2,medianColor:\'#0C5DA5\',medianWidth:3,stemColor:\'#A63400\',stemDashStyle:\'dot\',stemWidth:1,whiskerColor:\'#3D9200\',whiskerLength:\'20%\',whiskerWidth:3 } },
                           xAxis: { categories: [\'board #0\', \'board #1\'] },
                           yAxis: { title: { text: \'Luminosity/Brightness [lux]\' }, labels: { format: \'{value}\' } },
                           series:
                           [{
                              data: [['.$lux['boxplot'][2][0][0].','.$lux['boxplot'][2][0][1].','.$lux['boxplot'][2][0][2].','.$lux['boxplot'][2][0][3].','.$lux['boxplot'][2][0][4].'],
                                     ['.$lux['boxplot'][2][1][0].','.$lux['boxplot'][2][1][1].','.$lux['boxplot'][2][1][2].','.$lux['boxplot'][2][1][3].','.$lux['boxplot'][2][1][4].']]
                           }]
                        });
                     });
                    </script>';
      $content = '<!DOCTYPE html>
                  <!-- Report generated using I.E.Q.A. system -->
                  <html>
                  <head>
                  ' . $css1 . '
                  ' . $css2 . '
                  ' . $js1 . '
                  ' . $js2 . '
                  ' . $js3 . '
                  ' . $js_charts . '
                  </head>
                  <body>
                     <div class="container-fluid">
                        <div class="col-xs-12">
                           <h1 class="text-center text-primary">Report generated using I.E.Q.A. system</h1>
                        </div>
                        <div class="col-xs-12">
                           <h3><u>Network information</u></h3>
                        </div>
                        <div class="col-xs-offset-3 col-xs-6">
                           <table class="table table-striped">
                              <thead>
                                 <tr>
                                    <th></th>
                                    <th class="text-center">IP</th>
                                    <th class="text-center">Netmask</th>
                                    <th class="text-center">Network</th>
                                    <th class="text-center">Broadcast</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <td class="text-left" ><b>Server</b></td>
                                    <td class="text-right">192.168.43.100</td>
                                    <td class="text-right">255.255.255.0</td>
                                    <td class="text-right">192.168.43.0</td>
                                    <td class="text-right">192.168.43.255</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left" ><b>Board #0</b></td>
                                    <td class="text-right">192.168.43.10</td>
                                    <td class="text-right">255.255.255.0</td>
                                    <td class="text-right">192.168.43.0</td>
                                    <td class="text-right">192.168.43.255</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left" ><b>Board #1</b></td>
                                    <td class="text-right">192.168.43.20</td>
                                    <td class="text-right">255.255.255.0</td>
                                    <td class="text-right">192.168.43.0</td>
                                    <td class="text-right">192.168.43.255</td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        <div class="col-xs-12">
                           <h3><u>Statistical analysis</u></h3>
                        </div>
                        <div class="col-xs-4">
                           <h4 class="text-primary text-center">Temperature</h4>
                           <table class="table table-striped">
                              <thead>
                                 <tr>
                                    <th></th>
                                    <th class="text-center">Board #0</th>
                                    <th class="text-center">Board #1</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <td class="text-left"><b>min</b></td>
                                    <td class="text-right">' . $temp['table']['min'][0] . ' &deg; C</td>
                                    <td class="text-right">' . $temp['table']['min'][1] . ' &deg; C</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>max</b></td>
                                    <td class="text-right">' . $temp['table']['max'][0] . ' &deg; C</td>
                                    <td class="text-right">' . $temp['table']['max'][1] . ' &deg; C</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&mu;</b></td>
                                    <td class="text-right">' . $temp['table']['avg'][0] . ' &deg; C</td>
                                    <td class="text-right">' . $temp['table']['avg'][1] . ' &deg; C</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;<sup>2</sup></b></td>
                                    <td class="text-right">' . $temp['table']['variance'][0] . '</td>
                                    <td class="text-right">' . $temp['table']['variance'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;</b></td>
                                    <td class="text-right">' . $temp['table']['dev_std'][0] . '</td>
                                    <td class="text-right">' . $temp['table']['dev_std'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>V</b></td>
                                    <td class="text-right">' . $temp['table']['coeff_var'][0] . ' &#37;</td>
                                    <td class="text-right">' . $temp['table']['coeff_var'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>IRQ</b></td>
                                    <td class="text-right">' . $temp['table']['irq'][0] . '</td>
                                    <td class="text-right">' . $temp['table']['irq'][1] . '</td>
                                 </tr>
                              </tbody>
                           </table></br>
                           <div id="boxplot-temp"></div>
                        </div>
                        <div class="col-xs-4">
                           <h4 class="text-primary text-center">Relative humidity</h4>
                           <table class="table table-striped">
                              <thead>
                                 <tr>
                                    <th></th>
                                    <th class="text-center">Board #0</th>
                                    <th class="text-center">Board #1</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <td class="text-left"><b>min</b></td>
                                    <td class="text-right">' . $rh['table']['min'][0] . ' &#37;</td>
                                    <td class="text-right">' . $rh['table']['min'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>max</b></td>
                                    <td class="text-right">' . $rh['table']['max'][0] . ' &#37;</td>
                                    <td class="text-right">' . $rh['table']['max'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&mu;</b></td>
                                    <td class="text-right">' . $rh['table']['avg'][0] . ' &#37;</td>
                                    <td class="text-right">' . $rh['table']['avg'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;<sup>2</sup></b></td>
                                    <td class="text-right">' . $rh['table']['variance'][0] . '</td>
                                    <td class="text-right">' . $rh['table']['variance'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;</b></td>
                                    <td class="text-right">' . $rh['table']['dev_std'][0] . '</td>
                                    <td class="text-right">' . $rh['table']['dev_std'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>V</b></td>
                                    <td class="text-right">' . $rh['table']['coeff_var'][0] . ' &#37;</td>
                                    <td class="text-right">' . $rh['table']['coeff_var'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>IRQ</b></td>
                                    <td class="text-right">' . $rh['table']['irq'][0] . '</td>
                                    <td class="text-right">' . $rh['table']['irq'][1] . '</td>
                                 </tr>
                              </tbody>
                           </table></br>
                           <div id="boxplot-rh"></div>
                        </div>
                        <div class="col-xs-4">
                           <h4 class="text-primary text-center">Luminosity/Brightness</h4>
                           <table class="table table-striped">
                              <thead>
                                 <tr>
                                    <th></th>
                                    <th class="text-center">Board #0</th>
                                    <th class="text-center">Board #1</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <td class="text-left"><b>min</b></td>
                                    <td class="text-right">' . $lux['table']['min'][0] . ' lux</td>
                                    <td class="text-right">' . $lux['table']['min'][1] . ' lux</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>max</b></td>
                                    <td class="text-right">' . $lux['table']['max'][0] . ' lux</td>
                                    <td class="text-right">' . $lux['table']['max'][1] . ' lux</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&mu;</b></td>
                                    <td class="text-right">' . $lux['table']['avg'][0] . ' lux</td>
                                    <td class="text-right">' . $lux['table']['avg'][1] . ' lux</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;<sup>2</sup></b></td>
                                    <td class="text-right">' . $lux['table']['variance'][0] . '</td>
                                    <td class="text-right">' . $lux['table']['variance'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>&sigma;</b></td>
                                    <td class="text-right">' . $lux['table']['dev_std'][0] . '</td>
                                    <td class="text-right">' . $lux['table']['dev_std'][1] . '</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>V</b></td>
                                    <td class="text-right">' . $lux['table']['coeff_var'][0] . ' &#37;</td>
                                    <td class="text-right">' . $lux['table']['coeff_var'][1] . ' &#37;</td>
                                 </tr>
                                 <tr>
                                    <td class="text-left"><b>IRQ</b></td>
                                    <td class="text-right">' . $lux['table']['irq'][0] . '</td>
                                    <td class="text-right">' . $lux['table']['irq'][1] . '</td>
                                 </tr>
                              </tbody>
                           </table></br>
                           <div id="boxplot-lux"></div>
                        </div>
                        <div class="col-xs-12">
                           <hr style="border-color: #DDD; border-style: solid 2px; border-width: 2px 0;">
                           <footer class="footer">
                              <div class="container">
                                 <p class="text-muted">This report has been generated on ' . $date . '  -  <a href="http://creativecommons.org/licenses/by-nc/4.0/legalcode">I.E.Q.A. System (CC BY-NC 4.0) 2015</a></p>
                              </div>
                           </footer>
                        </div>
                     </div>
                  </body>
                  </html>';

      return $content;
   }

}
