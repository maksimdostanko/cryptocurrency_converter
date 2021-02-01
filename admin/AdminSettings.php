<?php

class AdminSettings
{
	private $frequency_value;

	public function __construct()
	{
		$this->frequency_value = get_option('frequency_value');
	}

	// Add our WP admin hooks.
	public function load()
	{
		add_action('admin_menu', [$this, 'add_plugin_options_page']);
		add_action('admin_init', [$this, 'add_plugin_settings']);
	}

	// Add our plugin's option page to the WP admin menu.
	public function add_plugin_options_page()
	{
		add_options_page(
			'Cryptocurrency converter',
			'Cryptocurrency',
			'manage_options',
			'currency_converter_settings',
			[$this, 'render_admin_page']
		);
	}

	// Render our plugin's option page.
	public function render_admin_page()
	{
		?>
		<div class="wrap">
			<h1>Converter Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('currency_converter_settings');
				do_settings_sections('currency_converter_settings');
				submit_button();
				?>
			</form>
		</div>
		<div class="wrap">
			<h1>Log:</h1>
			<table border="0">
				<tr>
					<th>Time</th>
					<th>IP</th>
					<th>Message</th>
				</tr>
				<?php
				$logger = new Logger();
				foreach ($logger->read(10) as $row) {
					echo "<tr><td> $row->log_date</td><td style='padding: 4px'><b>$row->remote_addr</b></td><td>$row->message</td></tr>";
				}
				?>
			</table>
		</div>
		<?php
	}

	// Initialize our plugin's settings.
	public function add_plugin_settings()
	{

		$args = array(
			'type' => 'string',
			'sanitize_callback' => [$this, 'sanitize_text_field'],
			'default' => NULL,
		);
		register_setting('currency_converter_settings', 'frequency_value', $args);

		add_settings_section(
			'ex_settings',
			'Cache time',
			[$this, 'render_licensing_instructions'],
			'currency_converter_settings'
		);

		add_settings_field(
			'frequency_value',
			'Update frequency (min 300)',
			[$this, 'frequency_value'],
			'currency_converter_settings',
			'ex_settings'
		);
	}

	// Render instructions for our plugin's licensing section.
	public function render_licensing_instructions()
	{
		print 'Enter cache time below:';
	}

	// Render
	public function frequency_value()
	{
		printf(
			'<input type="text" id="frequency_value" name="frequency_value" value="%s" /> sec',
			esc_attr($this->frequency_value)
		);
	}

	// Sanitize input from our plugin's option form and validate the provided key.
	public function sanitize_text_field($val)
	{
		if ($val<300){
			$val=300;
		}
		return $val;
	}
}
