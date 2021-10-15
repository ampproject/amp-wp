<?php

namespace PHPSTORM_META {

	override(
		\AmpProject\AmpWP\Services::get(),

		// TODO: I'd like to use AmpWpPlugin::SERVICES directly here but it doesn't seem to work.
		map( [
			'admin.analytics_menu'               => \AmpProject\AmpWP\Admin\AnalyticsOptionsSubmenu::class,
			'admin.google_fonts'                 => \AmpProject\AmpWP\Admin\GoogleFonts::class,
			'admin.onboarding_menu'              => \AmpProject\AmpWP\Admin\OnboardingWizardSubmenu::class,
			'admin.onboarding_wizard'            => \AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage::class,
			'admin.options_menu'                 => \AmpProject\AmpWP\Admin\OptionsMenu::class,
			'admin.support_screen'               => \AmpProject\AmpWP\Admin\SupportScreen::class,
			'admin.support'                      => \AmpProject\AmpWP\Admin\SupportLink::class,
			'admin.paired_browsing'              => \AmpProject\AmpWP\Admin\PairedBrowsing::class,
			'admin.plugin_row_meta'              => \AmpProject\AmpWP\Admin\PluginRowMeta::class,
			'admin.polyfills'                    => \AmpProject\AmpWP\Admin\Polyfills::class,
			'admin.user_rest_endpoint_extension' => \AmpProject\AmpWP\Admin\UserRESTEndpointExtension::class,
			'admin.validation_counts'            => \AmpProject\AmpWP\Admin\ValidationCounts::class,
			'amp_slug_customization_watcher'     => \AmpProject\AmpWP\AmpSlugCustomizationWatcher::class,
			'background_task_deactivator'        => \AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator::class,
			'cli.command_namespace'              => \AmpProject\AmpWP\CliCli\CommandNamespaceRegistration::class,
			'cli.optimizer_command'              => \AmpProject\AmpWP\CliCli\OptimizerCommand::class,
			'cli.transformer_command'            => \AmpProject\AmpWP\CliCli\TransformerCommand::class,
			'cli.validation_command'             => \AmpProject\AmpWP\CliCli\ValidationCommand::class,
			'css_transient_cache.ajax_handler'   => \AmpProject\AmpWP\Admin\ReenableCssTransientCachingAjaxAction::class,
			'css_transient_cache.monitor'        => \AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching::class,
			'dependency_support'                 => \AmpProject\AmpWP\DependencySupport::class,
			'dev_tools.block_sources'            => \AmpProject\AmpWP\DevTools\BlockSources::class,
			'dev_tools.callback_reflection'      => \AmpProject\AmpWP\DevTools\CallbackReflection::class,
			'dev_tools.error_page'               => \AmpProject\AmpWP\DevTools\ErrorPage::class,
			'dev_tools.file_reflection'          => \AmpProject\AmpWP\DevTools\FileReflection::class,
			'dev_tools.likely_culprit_detector'  => \AmpProject\AmpWP\DevTools\LikelyCulpritDetector::class,
			'dev_tools.user_access'              => \AmpProject\AmpWP\DevTools\UserAccess::class,
			'editor.editor_support'              => \AmpProject\AmpWP\Editor\EditorSupport::class,
			'extra_theme_and_plugin_headers'     => \AmpProject\AmpWP\ExtraThemeAndPluginHeaders::class,
			'injector'                           => \AmpProject\AmpWP\Infrastructure\Injector::class,
			'loading_error'                      => \AmpProject\AmpWP\LoadingError::class,
			'mobile_redirection'                 => \AmpProject\AmpWP\MobileRedirection::class,
			'obsolete_block_attribute_remover'   => \AmpProject\AmpWP\ObsoleteBlockAttributeRemover::class,
			'optimizer'                          => \AmpProject\AmpWP\Optimizer\OptimizerService::class,
			'optimizer.hero_candidate_filtering' => \AmpProject\AmpWP\Optimizer\HeroCandidateFiltering::class,
			'paired_routing'                     => \AmpProject\AmpWP\PairedRouting::class,
			'paired_url'                         => \AmpProject\AmpWP\PairedUrl::class,
			'plugin_activation_notice'           => \AmpProject\AmpWP\Admin\PluginActivationNotice::class,
			'plugin_registry'                    => \AmpProject\AmpWP\PluginRegistry::class,
			'plugin_suppression'                 => \AmpProject\AmpWP\PluginSuppression::class,
			'reader_theme_loader'                => \AmpProject\AmpWP\ReaderThemeLoader::class,
			'reader_theme_support_features'      => \AmpProject\AmpWP\ReaderThemeSupportFeatures::class,
			'rest.options_controller'            => \AmpProject\AmpWP\OptionsRESTController::class,
			'rest.validation_counts_controller'  => \AmpProject\AmpWP\Validation\ValidationCountsRestController::class,
			'sandboxing'                         => \AmpProject\AmpWP\Sandboxing::class,
			'save_post_validation_event'         => \AmpProject\AmpWP\Validation\SavePostValidationEvent::class,
			'server_timing'                      => \AmpProject\AmpWP\Instrumentation\ServerTiming::class,
			'site_health_integration'            => \AmpProject\AmpWP\Admin\SiteHealth::class,
			'support'                            => \AmpProject\AmpWP\Support\SupportCliCommand::class,
			'support_rest_controller'            => \AmpProject\AmpWP\Support\SupportRESTController::class,
			'url_validation_cron'                => \AmpProject\AmpWP\Validation\URLValidationCron::class,
			'url_validation_rest_controller'     => \AmpProject\AmpWP\Validation\URLValidationRESTController::class,
			'validated_url_stylesheet_gc'        => \AmpProject\AmpWP\BackgroundTask\ValidatedUrlStylesheetDataGarbageCollection::class,
			'validation.scannable_url_provider'  => \AmpProject\AmpWP\Validation\ScannableURLProvider::class,
			'validation.url_validation_provider' => \AmpProject\AmpWP\Validation\URLValidationProvider::class,
		] )
	);

	// For the injector, the return type should be the same as what the provided FQCN represents.
	override(
		\AmpProject\AmpWP\Infrastructure\Injector::make(),
		map( [ '' => '@' ] )
	);
}
