;(function(Vue, $){

    Vue.component('breadcrumb-section', {
        props: [
            'titleFont',
            'titleFontSize',
            'titleLineHeight',
            'titleFontWeight',
            'fieldName',
            'pageTitleState',
            'pageTitle',
            'breadcrumbPathState',
            'breadcrumbPath',
            'breadcrumbPathDelimiter',
            'contentAlignment',
            'backgroundColor',
            'titleColor',
            'contentColor',
            'linksColor',
            'backgroundImageOpacity',
            'backgroundImage',
            'backgroundImageUrl',
            'paddingTop',
            'paddingBottom',
            'paddingRight',
            'paddingLeft',
            'pageTitleBottomOffset',
            'parallax',

            // Translated strings
            'demoTitleText',
            'pageTitleStateText',
            'pageTitleText',
            'pageTitleBottomOffsetText',
            'breadcrumbPathStateText',
            'breadcrumbPathText',
            'breadcrumbPathDelimiterText',
            'contentAlignmentText',
            'contentAlignmentLeftText',
            'contentAlignmentCenterText',
            'contentAlignmentRightText',
            'backgroundColorText',
            'titleColorText',
            'contentColorText',
            'linksColorText',
            'backgroundImageOpacityText',
            'backgroundImageText',
            'removeBackgroundImageText',
            'paddingTopText',
            'paddingRightText',
            'paddingBottomText',
            'paddingLeftText',
            'parallaxText'
        ],
        template: '#breadcrumb-root-template',
        data: function() {
            var self = this;

            return {
                paddingTopInner: self.paddingTop,
                paddingRightInner: self.paddingRight,
                paddingBottomInner: self.paddingBottom,
                paddingLeftInner: self.paddingLeft,
                contentAlignmentInner: self.contentAlignment,
                backgroundColorInner: self.backgroundColor,
                backgroundImageUrlInner: self.backgroundImageUrl,
                backgroundImageInner: self.backgroundImage,
                breadcrumbPathDelimiterInner: self.breadcrumbPathDelimiter,
                titleColorInner: self.titleColor,
                contentColorInner: self.contentColor,
                linksColorInner: self.linksColor,
                breadcrumbPathStateInner: self.breadcrumbPathState,
                breadcrumbPathInner: self.breadcrumbPath,
                backgroundImageOpacityInner: self.backgroundImageOpacity,
                pageTitleBottomOffsetInner: self.pageTitleBottomOffset,
                pageTitleInnerState: self.pageTitleState,
                pageTitleInner: self.pageTitle,
                parallaxInner: self.parallax
            }
        },
        methods: {
            processFile: function($event) {
                this.bgImage = $event.target.files[0];
            },
            dynamicFieldName: function(fieldName) {
                return this.fieldName + '['+fieldName+']';
            },
            changeBreadcrumbPathState: function(event) {
                this.breadcrumbPathStateInner = event.target.checked;
            },
            changePageTitleState: function(event) {
                this.pageTitleInnerState = event.target.checked;
            },
            changeParallaxState: function(event) {
                this.parallaxInner = event.target.checked;
            }
        },
        computed: {
            containerStyleComputed: function() {
                var self = this;

                return {
                    paddingTop: self.paddingTopInner + 'px',
                    paddingRight: self.paddingRightInner + 'px',
                    paddingBottom: self.paddingBottomInner + 'px',
                    paddingLeft: self.paddingLeftInner + 'px',
                    backgroundColor: self.backgroundColorInner,
                    color: self.contentColorInner
                };
            },
            backgroundImageStyleComputed: function() {
                var self = this;

                return {
                    backgroundImage: self.backgroundImageUrlInner == 'none' ? self.backgroundImageUrlInner : 'url('+self.backgroundImageUrlInner+')',
                    opacity: self.backgroundImageOpacityInner
                };
            },
            classNames: function() {
                return ['breadcrumb-section-demo', this.contentAlignmentInner];
            },
            bgImageElementClassNames: function() {
                var self = this;

                return {
                    'breadcrumb-section-demo-bg-image' : true,
                    'breadcrumb-section-demo-bg-image--no-parallax': !self.parallaxInner
                };
            },
            titleStyle: function() {
                var self = this;

                return {
                    fontFamily: self.titleFont + ', sans-serif',
                    fontSize: self.titleFontSize,
                    lineHeight: self.titleLineHeight,
                    fontWeight: self.titleFontWeight,
                    color: self.titleColorInner,
                    marginBottom: self.pageTitleBottomOffsetInner + 'px'
                };
            },
            linksStyle: function() {
                var self = this;

                return {
                    color: self.linksColorInner,
                    backgroundImage: 'linear-gradient(to bottom, '+self.linksColorInner+' 100%, '+self.linksColorInner+' 100%)'
                }
            },
            parsedBreadcrumbPath: function() {
                return this.breadcrumbPathInner.replace(/\,\s/g, ',').split(',');
            }
        },
        updated: function() {
            var self = this,
                $el = $(this.$el);

            if($.MileniaAdmin && $.MileniaAdmin.LinkUnderliner) {
                $.MileniaAdmin.LinkUnderliner.toUnderline($el.find('.milenia-admin-underlined-link'));
            }
        },
        mounted: function() {
            var self = this,
                $el = $(this.$el),
                $sliders = $el.find('.breadcrumb-field-slider-instance'),
                $bgImage = $el.find('.breadcrumb-section-demo-bg-image'),
                $colorPickers = $el.find('.breadcrumb-field-color-picker'),
                $uploadBtns = $el.find('.breadcrumb-field-background-image-btn');

            if($bgImage.length && $.fn.parallax) $bgImage.parallax("50%",.2);

            if($sliders.length) {
                $sliders.slider({
                    min: 0,
                    step: 0.05,
                    max: 1,
                    value: self.backgroundImageOpacityInner,
                    slide: function(event, ui) {
                        self.backgroundImageOpacityInner = ui.value;
                    }
                });
            }

            if($colorPickers.length) {
                $colorPickers.wpColorPicker({
                    change: function(event, ui) {
                        self[event.target.getAttribute('data-v-model')] = event.target.value;
                    }
                });
            }

            if ($uploadBtns.length) {
                if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                    $uploadBtns.on('click', function(e) {
                        e.preventDefault();
                        var button = $(this),
                            $input = button.siblings('.breadcrumb-field-background-image-input');

                        try {
                            wp.media.editor.send.attachment = function(props, attachment) {
                                if($input.length) {
                                    self.backgroundImageUrlInner =  attachment.url;
                                    self.backgroundImageInner = attachment.id;
                                    $input.val(attachment.id);
                                }
                            };
                            wp.media.editor.open(button);
                        }
                        catch(error) {};
                        return false;
                    });
                }
            }

            $el.on('click.MileniaBreadcrumbRemoveBackgroundImage', '.breadcrumb-field-background-image-btn-remove',function(e) {
                var $btn = $(this),
                    $input = $btn.siblings('.breadcrumb-field-background-image-input');

                if($input.length) {
                    self.backgroundImageUrlInner = 'none';
                    self.backgroundImageInner = null;
                    $input.val('none');
                }
            });
        }
    });

    new Vue({
        el: '#breadcrumb-root'
    });

})(window.Vue, window.jQuery);
