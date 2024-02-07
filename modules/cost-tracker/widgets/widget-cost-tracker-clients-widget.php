<?php
/**
 * MainWP Logs Widget
 *
 * Displays the Logs Info.
 *
 * @package MainWP/Dashboard
 * @version 4.6
 */

namespace MainWP\Dashboard\Module\CostTracker;

use MainWP\Dashboard\MainWP_Utility;
/**
 * Class MainWP_Time_Tracker_Tasks_Widget
 */
class Cost_Tracker_Clients_Widget {

	/**
	 * Public static variable to hold the single instance of class.
	 *
	 * @var mixed Default null
	 */
	public static $instance = null;

	/**
	 * Method instance().
	 *
	 * Get instance class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor class.
	 *
	 * @return void
	 */
	public function __construct() {
		// todo.
	}


	/**
	 * Method callback_render_tasks_client_page_widget().
	 *
	 * Handle callback render tasks client page widget.
	 */
	public function callback_render_costs_widget() {
		if ( ! isset( $_GET['page'] ) || 'ManageClients' !== $_GET['page'] || empty( $_GET['client_id'] ) ) { //phpcs:ignore -- ok.
			return;
		}

		?>
		<div class="ui grid mainwp-widget-header">
			<div class="twelve wide column">
				<h3 class="ui header handle-drag">
					<?php esc_html_e( 'Cost Tracker', 'mainwp' ); ?>
					<div class="sub header"><?php esc_html_e( 'Manage and monitor your expenses.', 'mainwp' ); ?></div>
				</h3>
			</div>
		</div>
		<div class="mainwp-scrolly-overflow">
		<?php $this->render_costs_tracker_widget_content(); ?>
		</div>
		<div class="ui two columns grid mainwp-widget-footer">
			<div class="left aligned column">
				<a href="admin.php?page=ManageCostTracker" class="ui basic green mini fluid button"><?php esc_html_e( 'Cost Tracker Dashboard', 'mainwp' ); ?></a>
			</div>
		</div>
		<?php
	}


	/**
	 * Method render_tasks_client_page_widget_content().
	 */
	public function render_costs_tracker_widget_content() {
		$client_id = intval( $_GET['client_id'] ); //phpcs:ignore -- ok.
		$client_costs = Cost_Tracker_DB::get_instance()->get_cost_tracker_info_of_clients( array( $client_id ), array( 'with_sites' => true ) );

		if ( is_array( $client_costs ) ) {
			$client_costs = current( $client_costs ); // for current client.
		}

		if ( ! is_array( $client_costs ) ) {
			$client_costs = array();
		}

		?>
		<table class="ui table" id="mainwp-module-cost-tracker-costs-widget-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'mainwp' ); ?></th>  <?php //phpcs:ignore -- to fix WordPress word. ?>
					<th class="collapsing center aligned"><?php esc_html_e( 'Sites', 'mainwp' ); ?></th>
					<th class="collapsing right aligned"><?php esc_html_e( 'Price', 'mainwp' ); ?></th>
					<th class="no-sort collapsing"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ( ! empty( $client_costs ) ) {
				$columns = array(
					'name',
					'count_sites',
					'price',
					'actions',
				);
				foreach ( $client_costs as $cost ) {
					$item = $cost;

					?>
					<tr>
						<?php
						foreach ( $columns as $col ) {
							?>
							<td>
							<?php
							$row = $this->column_default( $item, $col );
							echo $row; // phpcs:igore -- ok.
							?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
				}
			}
			?>
			</tbody>
		</table>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				jQuery( '#mainwp-module-cost-tracker-costs-widget-table' ).DataTable( {
					"lengthMenu": [ [5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"] ],
					"stateSave" : true,
					"order"     : [ [1, 'asc'] ],
					"columnDefs": [ {
						"targets": 'no-sort',
						"orderable": false
					} ],
					"drawCallback": function () {
						mainwp_datatable_fix_menu_overflow('#mainwp-module-cost-tracker-costs-widget-table', -60, 5);
						jQuery( '#mainwp-module-cost-tracker-costs-widget-table .ui.dropdown' ).dropdown();
					}
				} );
				// to prevent events conflict.
				setTimeout( function () {
					mainwp_datatable_fix_menu_overflow('#mainwp-module-cost-tracker-costs-widget-table', -60, 5);
				}, 1000 );
			} );
		</script>
		<?php
	}


	/**
	 * Returns the column content for the provided item and column.
	 *
	 * @param array  $item         Record data.
	 * @param string $column_name  Column name.
	 * @return string $out Output.
	 */
	public function column_default( $item, $column_name ) {
		$out = '';

		switch ( $column_name ) {
			case 'name':
				$out = esc_html( $item->name );
				break;
			case 'count_sites': // for client widget.
				$out = property_exists( $item, 'cost_sites_ids' ) && is_array( $item->cost_sites_ids ) ? count( $item->cost_sites_ids ) : 0;
				break;
			case 'price': // for client widget.
				$out = Cost_Tracker_Utility::cost_tracker_format_price( $item->price );
				break;
			case 'actions':
				ob_start();
				?>
				<div class="ui right pointing dropdown icon mini basic green button not-auto-init" style="z-index: 999;">
						<i class="ellipsis horizontal icon"></i>
						<div class="menu">
							<a class="item widget-row-cost-tracker-edit-cost" href="admin.php?page=CostTrackerAdd&id=<?php echo intval( $item->id ); ?>"><?php esc_html_e( 'Edit', 'mainwp' ); ?></a>
						</div>										
					</div>		
				<?php
				$out = ob_get_clean();
				break;
			default:
		}
		return $out;
	}
}
