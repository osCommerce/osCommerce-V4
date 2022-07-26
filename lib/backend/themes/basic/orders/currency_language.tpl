								<div class="lc">
									<div class="wl-td">
										<label>{$smarty.const.TITLE_CURRENCY}<span class="fieldRequired">*</span></label>
										{tep_draw_pull_down_menu('currency', $entry->platform_currencies, $entry->defualt_platform_currency, 'maxlength="32" class="form-control"')}
									</div>                              
								</div>	
								<div class="lc">
									<div class="wl-td">
										<label>{$smarty.const.TEXT_LANGUAGES}<span class="fieldRequired">*</span></label>
										{tep_draw_pull_down_menu('language_id', $entry->platform_languages, $entry->defualt_platform_language, 'maxlength="32" class="form-control"')}
									</div>                              
								</div>	