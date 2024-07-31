<script type="x-template" id="breadcrumb-root-template">
    <div class="breadcrumb-field-container">
        <h4>{{demoTitleText}}:</h4>
        <div v-bind:class="classNames" v-bind:style="containerStyleComputed">
            <div v-bind:class="bgImageElementClassNames" v-bind:style="backgroundImageStyleComputed" v-show="backgroundImageInner"></div>

            <h1 class="breadcrumb-section-demo-page-title" v-bind:style="titleStyle" v-show="pageTitleInnerState && pageTitle.length">{{ pageTitleInner }}</h1>

            <nav class="breadcrumb-section-demo-path" v-show="breadcrumbPathStateInner && breadcrumbPathInner.length">
                <template v-for="page,index in parsedBreadcrumbPath">
                    <template v-if="index != parsedBreadcrumbPath.length - 1">
                        <span><a href="#" class="milenia-admin-underlined-link" v-bind:style="linksStyle">{{page}}</a></span>
                        {{breadcrumbPathDelimiterInner}}
                    </template>
                    <template v-else>
                        <span>{{page}}</span>
                    </template>
                </template>
            </nav>
        </div>

        <div class="breadcrumb-section-settings-row">
            <fieldset class="breadcrumb-section-settings-col-6">
                <div class="form-control">
                    <input type="checkbox" :checked="pageTitleInnerState" id="breadcrumb-field-page-title-state" :name="dynamicFieldName('page-title-state')" class="widefat monkeysan-admin-checkbox" @change="changePageTitleState($event)">
                    <label for="breadcrumb-field-page-title-state">{{pageTitleStateText}}</label>
                </div>

                <div class="form-control" v-show="pageTitleInnerState">
                    <label for="breadcrumb-field-page-title">{{pageTitleText}}</label>
                    <input type="text" id="breadcrumb-field-page-title" :name="dynamicFieldName('page-title')" class="widefat" v-model="pageTitleInner">
                </div>

                <div class="form-control" v-show="pageTitleInnerState">
                    <label for="breadcrumb-field-page-title-mb">{{pageTitleBottomOffsetText}}</label>
                    <input type="number" id="breadcrumb-field-page-title-mb" :name="dynamicFieldName('page-title-bottom-offset')" class="widefat" v-model="pageTitleBottomOffsetInner">
                </div>


                <div class="form-control">
                    <input type="checkbox" :checked="breadcrumbPathStateInner" id="breadcrumb-field-breadcrumb-path-state" :name="dynamicFieldName('breadcrumb-path-state')" class="widefat monkeysan-admin-checkbox" @change="changeBreadcrumbPathState($event)">
                    <label for="breadcrumb-field-breadcrumb-path-state">{{breadcrumbPathStateText}}</label>
                </div>

                <div class="form-control" v-show="breadcrumbPathStateInner">
                    <label for="breadcrumb-field-breadcrumb-path">{{breadcrumbPathText}}</label>
                    <input type="text" id="breadcrumb-field-breadcrumb-path" v-model="breadcrumbPathInner" :name="dynamicFieldName('breadcrumb-path')" class="widefat">
                </div>

                <div class="form-control" v-show="breadcrumbPathStateInner">
                    <label for="breadcrumb-field-breadcrumb-path-delimiter">{{breadcrumbPathDelimiterText}}</label>
                    <input type="text" id="breadcrumb-field-breadcrumb-path-delimiter" v-model="breadcrumbPathDelimiterInner" :name="dynamicFieldName('breadcrumb-path-delimiter')" class="widefat">
                </div>

                <div class="form-control">
                    <label class="form-radio-label">{{contentAlignmentText}}</label>

                    <input type="radio" id="breadcrumb-field-content-alignment-left" :name="dynamicFieldName('content-alignment')" value="text-left" v-model="contentAlignmentInner" class="widefat monkeysan-admin-radio">
                    <label for="breadcrumb-field-content-alignment-left">{{contentAlignmentLeftText}}</label>
                    <input type="radio" id="breadcrumb-field-content-alignment-center" :name="dynamicFieldName('content-alignment')" value="text-center" v-model="contentAlignmentInner" class="widefat monkeysan-admin-radio">
                    <label for="breadcrumb-field-content-alignment-center">{{contentAlignmentCenterText}}</label>
                    <input type="radio" id="breadcrumb-field-content-alignment-right" :name="dynamicFieldName('content-alignment')" value="text-right" v-model="contentAlignmentInner" class="widefat monkeysan-admin-radio">
                    <label for="breadcrumb-field-content-alignment-right">{{contentAlignmentRightText}}</label>
                </div>

                <div class="breadcrumb-section-settings-row">
                    <div class="breadcrumb-section-settings-col-3">
                        <div class="form-control">
                            <label for="breadcrumb-field-top-padding-desktop">{{paddingTopText}}</label>
                            <input type="number" id="breadcrumb-field-top-padding-desktop" :name="dynamicFieldName('padding-top')" v-model="paddingTopInner" class="widefat">
                        </div>
                    </div>

                    <div class="breadcrumb-section-settings-col-3">
                        <div class="form-control">
                            <label for="breadcrumb-field-right-padding-desktop">{{paddingRightText}}</label>
                            <input type="number" id="breadcrumb-field-right-padding-desktop" :name="dynamicFieldName('padding-right')" v-model="paddingRightInner" class="widefat">
                        </div>
                    </div>

                    <div class="breadcrumb-section-settings-col-3">
                        <div class="form-control">
                            <label for="breadcrumb-field-bottom-padding-desktop">{{paddingBottomText}}</label>
                            <input type="number" id="breadcrumb-field-bottom-padding-desktop" :name="dynamicFieldName('padding-bottom')" v-model="paddingBottomInner" class="widefat">
                        </div>
                    </div>

                    <div class="breadcrumb-section-settings-col-3">
                        <div class="form-control">
                            <label for="breadcrumb-field-left-padding-desktop">{{paddingLeftText}}</label>
                            <input type="number" id="breadcrumb-field-left-padding-desktop" :name="dynamicFieldName('padding-left')" v-model="paddingLeftInner" class="widefat">
                        </div>
                    </div>
                </div>

            </fieldset>

            <fieldset class="breadcrumb-section-settings-col-6">
                <div class="form-control">
                    <label for="breadcrumb-field-background-color">{{backgroundColorText}}</label><br>
                    <input type="text" id="breadcrumb-field-background-color" v-model="backgroundColorInner" data-v-model="backgroundColorInner" class="breadcrumb-field-color-picker" :name="dynamicFieldName('background-color')">
                </div>

                <div class="form-control">
                    <label for="breadcrumb-field-page-title-color">{{titleColorText}}</label><br>
                    <input type="text" id="breadcrumb-field-page-title-color" v-model="titleColorInner" data-v-model="titleColorInner" class="breadcrumb-field-color-picker" :name="dynamicFieldName('title-color')">
                </div>

                <div class="form-control">
                    <label for="breadcrumb-field-page-content-color">{{contentColorText}}</label><br>
                    <input type="text" id="breadcrumb-field-page-content-color" v-model="contentColorInner" data-v-model="contentColorInner" class="breadcrumb-field-color-picker" :name="dynamicFieldName('content-color')">
                </div>

                <div class="form-control">
                    <label for="breadcrumb-field-page-links-color">{{linksColorText}}</label><br>
                    <input type="text" id="breadcrumb-field-page-links-color" v-model="linksColorInner" data-v-model="linksColorInner" class="breadcrumb-field-color-picker" :name="dynamicFieldName('links-color')">
                </div>

                <div class="form-control">
                    <input type="hidden" class="breadcrumb-field-background-image-input" v-model="backgroundImageInner" :name="dynamicFieldName('background-image')">
                    <button type="button" class="button breadcrumb-field-background-image-btn" id="milenia-logo-vertical-dark-media">{{backgroundImageText}}</button>
                    <button type="button" class="button breadcrumb-field-background-image-btn-remove" id="milenia-logo-vertical-dark-media">{{removeBackgroundImageText}}</button>
                </div>

                <div class="form-control" v-show="backgroundImage">
                    <label for="breadcrumb-field-background-color">{{backgroundImageOpacityText}}</label>
                    <div class="breadcrumb-field-slider">
                        <input type="hidden" v-model="backgroundImageOpacityInner" class="breadcrumb-field-slider-input" :name="dynamicFieldName('background-image-opacity')">
                        <div class="breadcrumb-field-slider-instance"></div>
                    </div>
                </div>

                <div class="form-control">
                    <input type="checkbox" :checked="parallaxInner" id="breadcrumb-field-breadcrumb-parallax" :name="dynamicFieldName('parallax')" class="widefat monkeysan-admin-checkbox" @change="changeParallaxState($event)">
                    <label for="breadcrumb-field-breadcrumb-parallax">{{parallaxText}}</label>
                </div>
            </fieldset>
        </div>
    </div>
</script>
