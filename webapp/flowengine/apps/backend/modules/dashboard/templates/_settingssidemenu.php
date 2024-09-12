<?php

/**
 * _sidemenu.php template.
 *
 * Displays the main menu that is located on the left of the screen
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper('I18N');
if ($sf_user->mfHasCredential("code_access_rights")):
    ?>
    <div class="leftpanelinner">

        <ul class="nav nav-pills nav-stacked nav-bracket">

            <?php
            if ($sf_user->mfHasCredential("access_settings")) {
                if ($sf_user->mfHasCredential("access_content")) {
                    ?>
                    <li class="nav-parent  <?php if ($sf_context->getModuleName() == "content" || $sf_context->getModuleName() == "banner" || $sf_context->getModuleName() == "faq" || $sf_context->getModuleName() == "news" || $sf_context->getModuleName() == "languages" || $sf_context->getModuleName() == "announcements" || $sf_context->getModuleName() == "siteconfig") {
                        echo "nav-active active";
                    } ?>"><a href=""><i class="fa fa-list-alt"></i>
                            <span><?php echo __('Content'); ?></span></a>
                        <ul class="children" <?php if ($sf_context->getModuleName() == "content" || $sf_context->getModuleName() == "banner" || $sf_context->getModuleName() == "faq" || $sf_context->getModuleName() == "news" || $sf_context->getModuleName() == "languages" || $sf_context->getModuleName() == "announcements" || $sf_context->getModuleName() == "siteconfig") {
                            echo "style='display: block'";
                        } ?>>
                            <?php
                            if ($sf_user->mfHasCredential('managewebpages')) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "content") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/content/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Web Pages'); ?></a>
                                </li>

                                <li <?php if ($sf_context->getModuleName() == "banner") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/banner/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Banners'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("managefaqs")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "faq") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/faq/index"><i class="fa fa-caret-right"></i><?php echo __('FAQs'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("managenews")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "news") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/news/index"><i class="fa fa-caret-right"></i><?php echo __('News'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("managelanguages")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "languages") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/languages/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Languages'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageannouncements")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "announcements") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/announcements/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Announcements'); ?></a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </li>
                    <?php
                }

                if ($sf_user->mfHasCredential("access_workflow")) {
                    ?>
                    <li <?php if ($sf_context->getModuleName() == "services" || $sf_context->getModuleName() == "invoicetemplates" || $sf_context->getModuleName() == "permittemplates" || $sf_context->getModuleName() == "forms" || $sf_context->getModuleName() == "submenus") {
                        echo "class='active'";
                    } ?>>
                        <a href="/plan/services/index"><i class="fa fa-puzzle-piece"></i><?php echo __('Services'); ?></a>
                    </li>
                    <?php
                }

                if ($sf_user->mfHasCredential("access_forms")) {
                    ?>
                    <li class="nav-parent  <?php if ($sf_context->getModuleName() == "department" || $sf_context->getModuleName() == "formgroups" || $sf_context->getModuleName() == "feecategories" || $sf_context->getModuleName() == "invoiceapiaccounts" || $sf_context->getModuleName() == "fees" || $sf_context->getModuleName() == "permits" || $sf_context->getModuleName() == "jsonreports") {
                        echo "nav-active active";
                    } ?>"><a href=""><i class="fa fa-magic"></i>
                            <span><?php echo __('Other Settings'); ?></span></a>
                        <ul class="children" <?php if ($sf_context->getModuleName() == "department" || $sf_context->getModuleName() == "formgroups" || $sf_context->getModuleName() == "feecategories" || $sf_context->getModuleName() == "invoiceapiaccounts" || $sf_context->getModuleName() == "fees" || $sf_context->getModuleName() == "permits" || $sf_context->getModuleName() == "jsonreports" || $sf_context->getModuleName() == "applications") {
                            echo "style='display: block'";
                        } ?>>
                            <?php
                            if ($sf_user->mfHasCredential("manageformgroups")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "formgroups") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/formgroups/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Form Categories'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("access_forms")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "forms") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/forms/index/filter/all"><i
                                            class="fa fa-caret-right"></i><?php echo __('All Forms'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("managefees")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "feecategories") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/feecategories/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Fee Categories'); ?></a>
                                </li>

                                <li <?php if ($sf_context->getModuleName() == "fees") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/fees/index"><i class="fa fa-caret-right"></i><?php echo __('Fees'); ?></a>
                                </li>
                                <li <?php if ($sf_context->getModuleName() == "merchant") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/merchant/index') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Merchants'); ?></a>
                                </li>
                                <li <?php if ($sf_context->getModuleName() == "currencies") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/currencies/index') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Currencies'); ?></a>
                                </li>
                                <li <?php if ($sf_context->getModuleName() == "feecode") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/feecode/index') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Fee codes'); ?></a>
                                </li>
                                <li <?php if ($sf_context->getModuleName() == "zones") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/zones/index') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Fee zones'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageinvoices")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "invoiceapiaccounts") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/invoiceapiaccounts/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Invoice API Accounts'); ?></a>
                                </li>
                                <li <?php if ($sf_context->getModuleName() == "apicontent") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/apicontent/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('API content'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageformgroups")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "jsonreports") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/jsonreports/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('JSON Reports'); ?></a>
                                </li>
                                <?php
                            }
                            if ($sf_user->mfHasCredential("managemembership")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "applications") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/applications/showmemberships') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Membership database'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageagencies")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "agency") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/agency/index') ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Agencies') ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageplotsdisabled")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "plot") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo public_path('plan/plot/index'); ?>"><i
                                            class="fa fa-caret-right"></i><?php echo __('Plot Details'); ?></a>
                                </li>
                                <?php
                            }
                            if ($sf_user->mfHasCredential("access_workflow")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "workflow") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/workflow/indexCategory') ?>"><i
                                            class="fa fa-caret-right"></i>Service Categories</a>
                                </li>
                                <?php
                            }
                            if ($sf_user->mfHasCredential("manageagenda")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "agenda") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/agenda/index') ?>"><i class="fa fa-caret-right"></i>Agenda
                                        layout</a>
                                </li>
                                <?php
                            }
                            if ($sf_user->mfHasCredential("manageinvoices")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "invoicetemplates") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/invoicetemplates/list') ?>"><i
                                            class="fa fa-caret-right"></i>Invoice templates</a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </li>
                    <?php
                }

                if ($sf_user->mfHasCredential("access_security")) {
                    ?>
                    <li class="nav-parent  <?php if ($sf_context->getModuleName() == "usercategories" || $sf_context->getModuleName() == "groups" || $sf_context->getModuleName() == "credentials" || ($sf_context->getModuleName() == "wizard" && $sf_context->getActionName() == "security")) {
                        echo "nav-active active";
                    } ?>"><a href=""><i class="fa fa-unlock-alt"></i>
                            <span><?php echo __('Security'); ?></span></a>
                        <ul class="children" <?php if ($sf_context->getModuleName() == "usercategories" || $sf_context->getModuleName() == "groups" || $sf_context->getModuleName() == "credentials" || ($sf_context->getModuleName() == "wizard" && $sf_context->getActionName() == "security")) {
                            echo "style='display: block'";
                        } ?>>
                            <?php
                            if ($sf_user->mfHasCredential("managecategories")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "usercategories") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/usercategories/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('User Categories'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("managegroups")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "groups") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/groups/index"><i class="fa fa-caret-right"></i><?php echo __('Groups'); ?></a>
                                </li>
                                <?php
                            }

                            if ($sf_user->mfHasCredential("manageroles")) {
                                ?>
                                <li <?php if ($sf_context->getModuleName() == "credentials") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="/plan/credentials/index"><i
                                            class="fa fa-caret-right"></i><?php echo __('Roles'); ?></a>
                                </li>
                                <?php
                            }
                            if ($sf_user->mfHasCredential("signingsessions")) { ?>
                                <li <?php if ($sf_context->getModuleName() == "signingsessions") {
                                    echo "class='active'";
                                } ?>>
                                    <a href="<?php echo url_for('/plan/signingsessions/index') ?>">
                                        <i class="fa fa-edit"></i><?php echo __('Signing Sessions'); ?>
                                    </a>
                                </li>
                                <?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                }

                if ($sf_user->mfHasCredential("managewebpages")) {
                    ?>
                    <li <?php if ($sf_context->getModuleName() == "siteconfig") {
                        echo "class='active'";
                    } ?>>
                        <a href="/plan/siteconfig/index"><i class="fa fa-wrench"></i><?php echo __('Site Config'); ?></a>
                    </li>
                    <?php
                }
            }
            ?>
            <li>
                <hr>
            </li>

            <li><a href="/plan/dashboard"><i class="fa fa-dashboard"> </i> <?php echo __('Dashboard'); ?></a></li>
        </ul>


    </div><!-- leftpanelinner -->
    </div><!-- leftpanel -->
<?php endif; ?>