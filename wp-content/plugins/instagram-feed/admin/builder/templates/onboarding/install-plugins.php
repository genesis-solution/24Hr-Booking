<div class="sb-onboarding-wizard-step-wrapper sb-onboarding-wizard-step-installp sb-fs">

	<div class="sb-onboarding-wizard-step-top sb-fs" data-large="true">
		<h4 v-html="onboardingWizardStepContent['install-plugins'].heading"></h4>
		<span v-html="onboardingWizardStepContent['install-plugins'].description"></span>
	</div>

	<div class="sb-onboarding-wizard-elements-list sb-fs">

		<div class="sb-onboarding-wizard-elem sb-fs" v-for="plugin in onboardingWizardStepContent['install-plugins']?.pluginsList">
			<div class="sb-onboarding-wizard-elem-info">
				<div class="sb-onboarding-wizard-elem-icon" v-if="plugin?.icon !== undefined">
					<img :src="plugin?.icon" :alt="plugin?.heading"/>
				</div>
				<div class="sb-onboarding-wizard-elem-text">
					<strong v-if="plugin?.heading !== undefined" v-html="plugin?.heading"></strong>
					<span v-if="plugin?.description !== undefined" v-html="plugin?.description"></span>
				</div>

			</div>
			<div class="sb-onboarding-wizard-elem-toggle">
				<div  :data-color="plugin?.color" :data-active="switcherOnboardingWizardCheckActive(plugin)" :data-uncheck="plugin?.uncheck"  @click.prevent.default="switcherOnboardingWizardClick(plugin)"></div>
			</div>
		</div>

	</div>



</div>

<div class="sb-onboarding-wizard-step-pag-btns sb-fs">
	<button class="sb-btn sbi-btn-grey sb-btn-wizard-back" v-html="'Back'" @click.prevent.default="previousWizardStep"></button>
	<button class="sb-btn sbi-btn-blue sb-btn-wizard-next sb-btn-wizard-install" v-html="'Install Selected Plugins'" @click.prevent.default="nextWizardStep('submit')"></button>
</div>