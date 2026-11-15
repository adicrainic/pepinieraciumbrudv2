<?php
/**
 * Email Footer
 *
 * Overridden by Pepiniera Ciumbrud API plugin to display the branded red footer
 * with title, slogan, contact phone, email, address, and copyright.
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$email           = $email ?? null;
$mailer_instance = class_exists( 'PCS_Mailer' ) ? PCS_Mailer::instance() : null;

$store_name  = $mailer_instance ? $mailer_instance->get_default_from_name() : 'Pepiniera Ciumbrud';
$slogan      = $mailer_instance ? $mailer_instance->get_store_slogan() : 'Din 2000 · Satul Rozelor';
$store_phone = $mailer_instance ? $mailer_instance->get_store_phone() : '+40 765 152 832';
$phone_href  = preg_replace( '/\s+/', '', $store_phone );
$store_email = $mailer_instance ? $mailer_instance->get_default_from_email() : 'contact@pepinieraciumbrud.ro';
$address     = $mailer_instance ? $mailer_instance->get_store_address() : 'Strada Vasile Lucaci 77, Sat Ciumbrud, Jud. Alba, România';
$year        = gmdate( 'Y' );
?>
																		</div>
																	</td>
																</tr>
															</table>
															<!-- End Content -->
														</td>
													</tr>
												</table>
												<!-- End Body -->
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer" role="presentation" style="background-color: #7D0C0C; border-top: 3px solid #B21111;">
										<tr>
											<td valign="top" style="padding: 0;">
												<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
													<tr>
														<td colspan="2" valign="middle" id="credit" style="background-color: #7D0C0C; color: #FAF6EE; padding: 24px 28px; text-align: center; border: 0;">
															<div style="font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif; font-size: 19px; font-weight: 700; color: #FFFFFF; letter-spacing: 0.4px;">
																<?php echo esc_html( $store_name ); ?>
															</div>
															<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10px; text-transform: uppercase; letter-spacing: 2.2px; color: #F2BDBD; font-weight: 600; margin: 3px 0 12px;">
																<?php echo esc_html( $slogan ); ?>
															</div>
															<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #FAF6EE; margin-bottom: 6px;">
																<a href="tel:<?php echo esc_attr( $phone_href ); ?>" style="color: #FFFFFF; text-decoration: none; font-weight: 600;"><?php echo esc_html( $store_phone ); ?></a>
																&nbsp;&middot;&nbsp;
																<a href="mailto:<?php echo esc_attr( $store_email ); ?>" style="color: #FFFFFF; text-decoration: underline;"><?php echo esc_html( $store_email ); ?></a>
															</div>
															<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; color: #F2BDBD; margin-bottom: 10px;">
																<?php echo esc_html( $address ); ?>
															</div>
															<div style="font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; color: rgba(250, 246, 238, 0.72); border-top: 1px solid rgba(255, 255, 255, 0.14); padding-top: 10px;">
																&copy; <?php echo esc_html( $year ); ?> <?php echo esc_html( $store_name ); ?>. Toate drepturile rezervate.
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
			</tr>
		</table>
	</body>
</html>
