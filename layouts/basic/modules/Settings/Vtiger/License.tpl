{strip}
	<div class="settingsIndexPage">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<pre>
			{if $USERLANG eq 'pl_pl'}
				{include file="licenses/LicensePL.txt"}
			{else}
				{include file="licenses/LicenseEN.txt"}
			{/if}
		</pre>
	</div>
{/strip}
