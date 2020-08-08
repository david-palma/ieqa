<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

class IEQA extends CI_Controller
{
   public function __construct()
   {
      parent::__construct();

      // Load url from helper library
      $this->load->helper('url');

      // Load sessions library
      $this->load->library('session');

		// Load model
		$this->load->model('sensors');
   }

   /* Load home page if this controller is invoked */
   public function index()
   {
      $this->home();
   }

   /* Load the "home" view */
   public function home()
   {
      $data['title'] = "Home";
      $data['subtitle'] = "Indoor Environmental Quality Analyzer";
      $data['description'] = "Remote WiFi monitoring system... bla bla";

      $this->load->view('header', $data);
      $this->load->view('home', $data);
      $this->load->view('footer');
   }

   /* Load the "dashboard" view */
   public function dashboard()
   {
		if($this->sensors->is_not_empty('sensors'))
		{
			$data['title'] = "Dashboard";
			$data['subtitle'] = "Display relevant data";
			$data['description'] = "Using charts and tables to illustrate the most important informations";

			$this->load->view('header', $data);
			$this->load->view('dashboard', $data);
			$this->load->view('footer');
		}
		else
		{
			$data['title'] = "Error";
			$data['subtitle'] = "Whoops, look like something went wrong...";
			$data['description'] = "The website encountered an error";

			$this->load->view('header', $data);
			$this->load->view('db_empty', $data);
			$this->load->view('footer');
		}
   }

   /* Load the "analytics" view */
   public function analytics()
   {
		if($this->sensors->is_not_empty('sensors'))
		{
			$data['title'] = "Analytics";
			$data['subtitle'] = "Analyze data";
			$data['description'] = "Using graphs and statistics to analyze data";

			$this->load->view('header', $data);
			$this->load->view('analytics', $data);
			$this->load->view('footer');
		}
		else
		{
			$data['title'] = "Error";
			$data['subtitle'] = "Whoops, look like something went wrong...";
			$data['description'] = "The website encountered an error";

			$this->load->view('header', $data);
			$this->load->view('db_empty', $data);
			$this->load->view('footer');
		}
   }

   /* Load the "graphs" view */
   public function graphs()
   {
      if($this->sensors->is_not_empty('sensors'))
		{
			$data['title'] = "Graphs";
			$data['subtitle'] = "Graph analysis";
			$data['description'] = "Using graphs to analyze data";

			$this->load->view('header', $data);
			$this->load->view('graphs', $data);
			$this->load->view('footer');
		}
		else
		{
			$data['title'] = "Error";
			$data['subtitle'] = "Whoops, look like something went wrong...";
			$data['description'] = "The website encountered an error";

			$this->load->view('header', $data);
			$this->load->view('db_empty', $data);
			$this->load->view('footer');
		}
   }

   /* Load the "statistics" view */
   public function statistics()
   {
		if($this->sensors->is_not_empty('sensors'))
		{
			$data['title'] = "Statistics";
			$data['subtitle'] = "Statistical analysis";
			$data['description'] = "Using statistics and graphs to make statistical analysis of data";

			$this->load->view('header', $data);
			$this->load->view('statistics', $data);
			$this->load->view('footer');
		}
		else
		{
			$data['title'] = "Error";
			$data['subtitle'] = "Whoops, look like something went wrong...";
			$data['description'] = "The website encountered an error";

			$this->load->view('header', $data);
			$this->load->view('db_empty', $data);
			$this->load->view('footer');
		}
   }

   /* Load the "report" view */
   public function report()
   {
		if($this->sensors->is_not_empty('sensors'))
		{
			$data['title'] = "Report";
			$data['subtitle'] = "Report";
			$data['description'] = "Using the processed data to make statistical report";

			$this->load->view('header', $data);
			$this->load->view('report', $data);
			$this->load->view('footer');
		}
		else
		{
			$data['title'] = "Error";
			$data['subtitle'] = "Whoops, look like something went wrong...";
			$data['description'] = "The website encountered an error";

			$this->load->view('header', $data);
			$this->load->view('db_empty', $data);
			$this->load->view('footer');
		}
   }

   /* Load the "options" view */
   public function options()
   {
      $data['title'] = "Options";
      $data['subtitle'] = "Options";
      $data['description'] = "It is possible to edit various options";
      $data['message'] = "";

      $this->load->view('header', $data);
      $this->load->view('options', $data);
      $this->load->view('footer');
   }

   /* Load the "info" view */
   public function info()
   {
      $data['title'] = "About us";
      $data['description'] = "Information about us and contacts";

      $this->load->view('header', $data);
      $this->load->view('info', $data);
      $this->load->view('footer');
   }

   /* Generates report in HTML format */
   function generate_report()
   {
      if($this->input->server('REQUEST_METHOD') != 'POST')
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
         return;
      }
      $temp = json_decode($this->sensors->get_json_statistics(0), true);
      $rh   = json_decode($this->sensors->get_json_statistics(1), true);
      $lux  = json_decode($this->sensors->get_json_statistics(2), true);

      $report = $this->sensors->make_report($temp, $rh, $lux);

      echo $report;
   }

   /* Echo of data in JSON format for table */
   function json_data_table($b)
   {
      $json_table = $this->sensors->get_json_table($b);
      echo $json_table;
   }

   /* Echo of data in JSON format for graphs */
   function json_data_graphs($b)
   {
      $json_graphs = $this->sensors->get_json_graphs($b);
      echo $json_graphs;
   }

   /* Echo of data in JSON format for statistics */
   function json_data_statistics($par)
   {
      $json_stats = $this->sensors->get_json_statistics($par);
      echo $json_stats;
   }

   /* Function that updates the sampling time in the table `thresholds` of the database by using sql query */
   function edit_samp_time()
   {
      if($this->input->server('REQUEST_METHOD') == 'POST')
      {
         $ret = $this->sensors->update_samp_time();
      }
      else
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
      }

      if($ret === 1)
         echo 1;
      else
         echo 0;
   }

   /* Function that updates the threshold values in the table `thresholds` of the database by using sql query */
   function edit_threshold_values()
   {
      if($this->input->server('REQUEST_METHOD') == 'POST')
      {
         $ret = $this->sensors->update_threshold_values();
      }
      else
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
      }

      if($ret === 1)
         echo 1;
      else
         echo 0;
   }

   /* Function that eliminates the table `sensors` of the database by using sql query (TRUNCATE) */
   function empty_table()
   {
      if($this->input->server('REQUEST_METHOD') != 'POST')
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
         return;
      }

      if($this->sensors->is_not_empty('sensors'))
      {
         $this->db->truncate('sensors');
         echo 1;
      }
      else
         echo 0;
   }

   /* Function that returns 0 if there are no data available for the selected board */
   function data_available($board)
   {
      $this->db->where('board', $board);
      $query = $this->db->get('sensors');

      if ($query->num_rows() > 0)
         echo 1;
      else
         echo 0;
   }

   /* Function that makes a ping request to a specific host */
   function ping($host)
   {
      $fp = fsockopen($host, 22, $errorCode, $errorCode, 1);

      if($fp === false)
         echo 0;
      else
         echo 1;
   }

   /* Function that provide the unix timestamp to the board */
   function get_time()
   {
      if($this->input->server('REQUEST_METHOD') == 'GET')
      {
         $result = "{" . date("Y-m-d H:i:s", time()) . "}";
         echo $result;
      }
      else
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
         return;
      }
   }

   /* Function that get thresholds (and samp. time) data stored into the database */
   function get_threshold_values($b)
   {
      if($this->input->server('REQUEST_METHOD') == 'GET')
      {
         $result = $this->sensors->get_thresholds($b);
         echo $result;
      }
      else
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
         return;
      }
   }

   /* Function that inserts data posted by the boards into the database */
   function insert_data()
   {
      if($this->input->server('REQUEST_METHOD') == 'POST')
      {
         $this->sensors->insert_data_into_db();
      }
      else
      {
         $data['title'] = 'Error';
         $data['error'] = 'This method cannot be invoked directly';
         $this->load->view('error', $data);
      }

      return;
   }
}
