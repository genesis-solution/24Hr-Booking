<div class="sbi-integration-popup-modal sb-fs-boss sbi-fb-center-boss" v-if="viewsActive.automatorIntegrationModal">
	<div class="sbi-integration-popup sbi-fb-popup-inside" >
        <div class="sbi-fb-popup-cls" @click.prevent.default="activateView('automatorIntegrationModal')">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#141B38"/>
            </svg>
        </div>
        <div class="sbi-popup-content">
            <div class="sbi-popup-content-header">
                <img :src="uncannyAutomatorScreen.integrationLogo" alt="">
                <h3>{{uncannyAutomatorScreen.heading}}</h3>
                <p>{{uncannyAutomatorScreen.description}}</p>
            </div>
            <div class="sbi-popup-ua-integration-steps">
                <div class="sbi-popup-ua-integration-step">
                    <div class="sbi-left">
                        <h4>{{uncannyAutomatorScreen.installStep.title}}</h4>
                        <p>{{uncannyAutomatorScreen.installStep.description}}</p>
                        <button class="sbi-btn sbi-btn-install" @click.prevent.default="installAutomatorPlugin(uncannyAutomatorScreen.isPluginInstalled, uncannyAutomatorScreen.isPluginActive, uncannyAutomatorScreen.pluginDownloadPath, uncannyAutomatorScreen.automatorPlugin)" :disabled="disableAutomatorBtn">
                            <span v-html="automatorInstallBtnIcon()"></span>
                            <span v-html="automatorInstallBtnText()"></span>
                        </button>
                    </div>
                    <img :src="uncannyAutomatorScreen.installStep.icon" alt="" class="sbi-step-image">
                </div>
                <div class="sbi-popup-ua-integration-step sbi-popup-ua-setup-step">
                    <div class="sbi-left">
                        <h4>{{uncannyAutomatorScreen.setupStep.title}}</h4>
                        <p>{{uncannyAutomatorScreen.setupStep.description}}</p>
                        <button class="sbi-btn" :disabled="!enableAutomatorSetupStep" @click.prevent.default="setupAutomatorPlugin()">Set up Plugin</button>
                    </div>
                    <img :src="uncannyAutomatorScreen.setupStep.icon" alt="" class="sbi-step-image">
                </div>
            </div>
        </div>
	</div>
</div>