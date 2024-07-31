<div class="sb-onboarding-wizard-step-wrapper sb-onboarding-wizard-step-configuref sb-fs">

	<div class="sb-onboarding-wizard-step-top sb-fs">
		<strong v-html="onboardingWizardStepContent['configure-features'].smallHeading"></strong>
		<h4 v-html="onboardingWizardStepContent['configure-features'].heading"></h4>
	</div>

	<div class="sb-onboarding-wizard-elements-list sb-fs">

		<div class="sb-onboarding-wizard-elem sb-fs" v-for="feature in onboardingWizardStepContent['configure-features']?.featuresList">
			<div class="sb-onboarding-wizard-elem-info">
				<div class="sb-onboarding-wizard-elem-icon" v-if="feature?.icon !== undefined" v-html="feature?.icon"></div>
				<div class="sb-onboarding-wizard-elem-text">
					<strong v-if="feature?.heading !== undefined" v-html="feature?.heading"></strong>
					<span v-if="feature?.description !== undefined" v-html="feature?.description"></span>
				</div>

			</div>
			<div class="sb-onboarding-wizard-elem-toggle">
				<div  :data-color="feature?.color" :data-active="switcherOnboardingWizardCheckActive(feature)" :data-uncheck="feature?.uncheck" @click.prevent.default="switcherOnboardingWizardClick(feature)"></div>
			</div>
		</div>

	</div>

	<div class="sb-onboarding-wizard-elements-list sb-fs" v-if="onboardingWizardStepContent['configure-features']?.proFeaturesList !== undefined">
		<div class="sb-onboarding-wizard-elements-list-hd sb-fs">
			<?php echo __( 'Pro Features') ?>
		</div>
		<div class="sb-onboarding-wizard-elem sb-fs" v-for="feature in onboardingWizardStepContent['configure-features']?.proFeaturesList">
			<div class="sb-onboarding-wizard-elem-info">
				<div class="sb-onboarding-wizard-elem-icon" v-if="feature?.icon !== undefined" v-html="feature?.icon"></div>
				<div class="sb-onboarding-wizard-elem-text">
					<strong v-if="feature?.heading !== undefined" v-html="feature?.heading"></strong>
					<span v-if="feature?.description !== undefined" v-html="feature?.description"></span>
				</div>

			</div>
			<div class="sb-onboarding-wizard-elem-toggle">
				<div  :data-color="feature?.color" :data-active="switcherOnboardingWizardCheckActive(feature)" :data-uncheck="feature?.uncheck"  @click.prevent.default="switcherOnboardingWizardClick(feature)"></div>
			</div>
		</div>

	</div>

</div>

<div class="sb-onboarding-wizard-step-pag-btns sb-fs">
	<button class="sb-btn sbi-btn-grey sb-btn-wizard-back" v-html="'Back'" @click.prevent.default="previousWizardStep"></button>
	<button class="sb-btn sbi-btn-blue sb-btn-wizard-next" v-html="'Next'" @click.prevent.default="nextWizardStep"></button>
</div>