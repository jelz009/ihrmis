<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Integrated Human Resource Management Information System
 *
 * An Open source Application Software use by Government agencies for 
 * management of employees Attendance, Leave Administration, Payroll, 
 * Personnel Training, Service Records, Performance, Recruitment,
 * Personnel Schedule(Plantilla) and more...
 *
 * @package		iHRMIS
 * @author		Manny Isles
 * @copyright	Copyright (c) 2008 - 2013, Charliesoft
 * @license		http://charliesoft.net/ihrmis/license
 * @link		http://charliesoft.net
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * iHRMIS Conversion Table Class
 *
 * This class use for converting number of minutes late
 * to the corresponding equivalent to leave credits.
 *
 * @package		iHRMIS
 * @subpackage	Models
 * @category	Models
 * @author		Manny Isles
 * @link		http://charliesoft.net/hrmis/user_guide/models/conversion_table.html
 */
class Manual_Manage extends MX_Controller {

	// --------------------------------------------------------------------
	
	function __construct()
    {
        parent::__construct();
		
		$this->load->model('options');
		
		//$this->output->enable_profiler(TRUE);
    }  
	
	// --------------------------------------------------------------------
	
	function cancel_office_pass($id)
	{
		// Delete from leave card first
		$info = $this->Office_pass->get_pass_info( $id );
		
		$this->Leave_card->delete_pass_slip($info['employee_id'], $info['date']);
		
		$this->Office_pass->cancel_office_pass($id);
		
		$data['msg'] =  'Office Pass set!';
		
		$this->session->set_flashdata('msg', 'Office Pass/ Pass slip cancelled!');
		
		redirect(base_url().'manual_manage/office_pass', 'refresh');
	}
	
	// --------------------------------------------------------------------
	
	function cancel_cto($id = '', $employee_id = '')
	{
		$c = new Compensatory_timeoff();
		$c->get_by_id($id);
		$c->delete();
		
		$this->Dtr->cancel_cto($id, $employee_id);
		
		$this->session->set_flashdata('msg', 'Compensatory Timeoff has been cancelled!');
		
		redirect(base_url().'manual_manage/cto/'.$employee_id, 'refresh');
	}
	
	// --------------------------------------------------------------------
	
	function login()
	{
		
		$data['page_name'] = '<b>Manual Login/Logout</b>';
		
		$data['msg'] = '';
	
		$data['options'] 					= $this->options->office_options();
		$data['selected'] 					= $this->session->userdata('office_id');
	
		//Months
		$data['month_options'] 		= $this->options->month_options();
		$data['month_selected'] 	= date('m');
		
		//Days
		$data['days_options'] 		= $this->options->days_options();
		$data['days_selected'] 		= date('d');
		
		$data['year_options'] 		= $this->options->year_options(2009, 2020);//2010 - 2020
		$data['year_selected'] 		= date('Y');
				
		$data['main_content'] = 'login';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function cto( $employee_id = '' )
	{
		$data['page_name'] = '<b>Compensatory Time Off</b>';
		$data['msg'] = '';
		
		//Months
		$data['month_options'] 		= $this->options->month_options();
		$data['month_selected'] 	= date('m');
		
		$data['year_options'] 		= $this->options->year_options(2009, 2020);//2010 - 2020
		$data['year_selected'] 		= date('Y');
		
		$data['leave_type_options'] = $this->options->leave_type_options();
		$data['leave_type_selected']= '';
		
		$data['employee_id']		= $employee_id;
				
		$data['main_content'] = 'cto';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function cto_apps()
	{
		
		$data['page_name'] = '<b>CTO Applications</b>';
		$data['msg'] = '';
		
		$this->load->library('pagination');
		
		$data['rows'] = $this->Compensatory_timeoff->get_cto_apps();
		
		// If leave manager get only the leave apps for his/ her office
		if ($this->session->userdata('user_type') == 5)
		{
			$this->Compensatory_timeoff->office_id = $this->session->userdata('office_id');
			$this->Compensatory_timeoff->get_cto_apps();
		}
		
		$config['base_url'] = base_url().'manual_manage/cto_apps';
		$config['total_rows'] = $this->Compensatory_timeoff->num_rows;
	    $config['per_page'] = '15';
	    $this->config->load('pagination', TRUE);
		
		$pagination = $this->config->item('pagination');
		
		// We will merge the config file of pagination
		$config = array_merge($config, $pagination);
		
		$this->pagination->initialize($config);
		
		// If leave manager get only the leave apps for his/ her office
		if ($this->session->userdata('user_type') == 5)
		{
			$this->Compensatory_timeoff->office_id = $this->session->userdata('office_id');
		}
		
		$data['rows'] = $this->Compensatory_timeoff->get_cto_apps($config['per_page'], $this->uri->segment(3));

		if ($this->input->post('op') == 1)
		{
			$data['rows'] = $this->Compensatory_timeoff->search_cto_apps($this->input->post('tracking_no'));
			
			if ( $this->input->post('tracking_no') == '' )
			{
				$data['rows'] = $this->Compensatory_timeoff->get_cto_apps($config['per_page'], $this->uri->segment(3));
			}
			
			if ( $this->input->post('tracking_no') != '')
			{
				$config['total_rows'] = 0;
				$this->pagination->initialize($config);
			}
			
		}
				
		$data['main_content'] = 'cto_apps';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function cto_forward_balance( $office_id = '' )
	{
		$data['page_name'] = '<b>CTO Forward Balance</b>';
		$data['msg'] = '';
		
		$data['error_msg'] = '';
		
		// Use for office listbox
		$data['options'] 			= $this->options->office_options();
		$data['selected'] 			= $this->session->userdata('office_id');
				
		$data['main_content'] = 'cto_forward_balance';
		
		$this->load->view('includes/template', $data);
		
	}
	
	// --------------------------------------------------------------------
	
	function office_pass()
	{
		
		$data['page_name'] = '<b>Office Pass</b>';
		$data['msg'] = '';
		
		//Months
		$data['month_options'] 		= $this->options->month_options();
		$data['month_selected'] 	= date('m');
		
		//
		$data['days_options'] 		= $this->options->days_options();
		//$data['days_selected'] 		= 1;//date('d');
		
		//
		$data['days_options'] 		= $this->options->days_options();
		$data['days_selected'] 		= date('d');
		
		$data['year_options'] 		= $this->options->year_options(2009, 2020);//2010 - 2020
		$data['year_selected'] 		= date('Y');
		
		if ($this->input->post('op'))
		{
			
			$seconds = strtotime($this->input->post('hour2').':'.$this->input->post('minute2'). ' '.$this->input->post('am_pm2')) - 
					   strtotime($this->input->post('hour').':'.$this->input->post('minute'). ' '.$this->input->post('am_pm'));
			
			if ($this->input->post('am_pm') == 'AM' && $this->input->post('am_pm2') == 'PM')
			{
				$seconds = strtotime('12:00 PM') - strtotime($this->input->post('hour').':'.$this->input->post('minute'). ' '.$this->input->post('am_pm'));
				
				$seconds += strtotime($this->input->post('hour2').':'.$this->input->post('minute2'). ' '.$this->input->post('am_pm2')) - strtotime("01:00 PM");
			}
			
			$info = array(
						'employee_id' 	=> $this->input->post('employee_id'),
						'date'		 	=> $this->input->post('year').'-'.$this->input->post('month').'-'.$this->input->post('day'),
						'time_out'	  	=> $this->input->post('hour').':'.$this->input->post('minute'). ' '.$this->input->post('am_pm'),
						'time_in'	 	=> $this->input->post('hour2').':'.$this->input->post('minute2'). ' '.$this->input->post('am_pm2'),
						'seconds'	  	=> $seconds,
						'date_acquired'	=> date('Y-m-d')
			);
			
			$this->Office_pass->insert_office_pass($info);
			
			// Insert to leave card but not yet active
			
			$card_month = $this->Helps->get_month_name($this->input->post('month'));
			
			$particulars = 'Pass Slip';
			
			$last_day = $this->Helps->get_last_day($this->input->post('month'), $this->input->post('year'));
		
			$last_day = $this->input->post('year').'-'.$this->input->post('month').'-'.$last_day;
			
			$v_abs = $this->Leave_conversion_table->compute_hour_minute($seconds);
			
			$action_take = substr($card_month, 0, 3).'. '.$this->input->post('year').' Pass Slip'; 
			
			$pass_slip_date = $this->input->post('year').'-'.$this->input->post('month').'-'.$this->input->post('day');
			
			$this->Leave_card->insert_entry(
											$this->input->post('employee_id'), 
											$particulars, 
											$v_abs, 
											$action_take, 
											$last_day, 
											0, 
											$pass_slip_date
											);
			
			
			$data['msg'] =  'Office Pass/ Pass slip set!';
			
			$this->session->set_flashdata('msg', 'Office Pass/ Pass slip set!');
		}
		
		
		$data['main_content'] = 'office_pass';
		
		$this->load->view('includes/template', $data);
		
	}
}	

/* End of file manual_manage.php */
/* Location: ./system/application/modules/manual_manage/controllers/manual_manage.php */