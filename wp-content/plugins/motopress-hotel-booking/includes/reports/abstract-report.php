<?php

namespace MPHB\Reports;

abstract class AbstractReport {

	/**
	 * @var string
	 */
	public $reportType;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {}

	abstract public function renderFilters();

	abstract public function renderReport();

	public function renderReportTypes() {
		$reportTypes = ReportFilters::getReportTypes();
		if ( ! empty( $reportTypes ) && count( $reportTypes ) > 1 ) {
			?>
			<div class="tablenav top">
				<div class="alignleft actions">
					<form id="mphb-select-report-type" method="get">
						<input type="hidden" name="page" value="mphb_reports">
						<?php
						if ( isset( $atts['range'] ) ) {
							?>
							<input type="hidden" name="range" value="<?php echo esc_attr( $atts['range'] ); ?>">
							<?php
						}
						?>
						<select id="mphb-report-types-list" name="report_type">
							<?php
							foreach ( $reportTypes as $reportType ) {
								?>
								<option value="<?php echo esc_attr( $reportType['type'] ); ?>" <?php echo $this->reportType == $reportType['type'] ? 'selected="selected"' : ''; ?>><?php echo esc_html( $reportType['description'] ); ?></option>
								<?php
							}
							?>
						</select>
						<input id="mphb-submit-report" class="button" type="submit" name="submit" value="<?php esc_attr_e( 'Show', 'motopress-hotel-booking' ); ?>">
					</form>
				</div>
			</div>
			<?php
		}
	}

	public function render() {
		?>
		<div class="mphb-earnings-report-wrapper">
			<?php
			$this->renderReportTypes();
			$this->renderReportBox();
			?>
		</div>
		<?php
	}

	abstract public function renderReportTitle();

	public function renderReportBox() {
		?>
		<div class="postbox mphb-earnings-reports">
			<?php
				$this->renderReportTitle();
			?>
			<div class="inside">
				<?php
				$this->renderFilters();
				$this->renderReport();
				?>
			</div>
		</div>
		<?php
		do_action( 'mphb_after_admin_report_output', $this->reportType );
	}

	/**
	 *
	 * @return string
	 */
	public function getReportType() {
		return $this->reportType;
	}
}

?>
