<?php
/**
 * _banner.php template.
 *
 * Displays banner
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
$site_settings = Functions::site_settings();

if(sizeof($banners) > 0)
{
?>

        <section class="slider_02">
            <div class="rev_slider_wrapper">
                <div id="rev_slider_2" class="rev_slider fullwidthabanner" style="display:none;" data-version="5.4.1">
                    <ul>
                    <?php foreach($banners as $banner): ?>
                        <li data-index="rs-<?php echo $banner->getId() ?>" data-transition="random" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off"  data-easein="Power3.easeInOut" data-easeout="Power3.easeInOut" data-masterspeed="1500"  data-thumb="<?php echo '/'.$site_settings->getUploadDir().'/'.$banner->getImage(); ?>"  data-rotate="0"  data-fstransition="fade" data-fsmasterspeed="1500" data-fsslotamount="7" data-saveperformance="off"  data-title="Intro" data-param1="" data-param2="" data-param3="" data-param4="" data-param5="" data-param6="" data-param7="" data-param8="" data-param9="" data-param10="" data-description="">
                            <img src="<?php echo '/'.$site_settings->getUploadDir().'/'.$banner->getImage(); ?>"  alt=""  data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="10" class="rev-slidebg" data-no-retina>
                            <div class="tp-caption  tp-resizeme"
                                 data-x="['center','center','center','center']" 
                                 data-hoffset="['0','0','0','0']" 

                                 data-y="['top','top','top','top']" 
                                 data-voffset="['9', '70', '70', '70']" 

                                 data-fontsize="['30','30','30','30']"
                                 data-fontweight="300"
                                 data-lineheight="['30','30','30','30']"
                                 data-width="none"
                                 data-height="none"
                                 data-whitespace="nowrap"
                                 data-color="#FFF"

                                 data-type="text" 
                                 data-responsive_offset="on" 

                                 data-frames='[{"delay":1200,"speed":2000,"frame":"0","from":"y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;","to":"o:1;","ease":"Power4.easeInOut"},
                                 {"delay":"wait","speed":300,"frame":"999","to":"auto:auto;","ease":"Power3.easeInOut"}]'

                                 data-textAlign="['center','center','center','center']"
                                 data-paddingtop="[0,0,0,0]"
                                 data-paddingright="[0,0,0,0]"
                                 data-paddingbottom="[0,0,0,0]"
                                 data-paddingleft="[0,0,0,0]"

                                 style="z-index: 5; white-space: nowrap; text-transform: capitalize;"><?php echo __("KEDAMS") ?></div>
                            <div class="tp-caption barlow tp-resizeme bigHeading"
                                 data-x="['center','center','center','center']" 
                                 data-hoffset="['0','0','0','0']" 

                                 data-y="['middle','middle','middle','middle']" 
                                 data-voffset="['-85', '-30', '-50', '-90']" 

                                 data-fontsize="['80','70','60','20']"
                                 data-fontweight="400"
                                 data-lineheight="['110','100','90','40']"
                                 data-width="none"
                                 data-height="none"
                                 data-whitespace="nowrap"
                                 data-color="#FFF"

                                 data-type="text" 
                                 data-responsive_offset="on" 

                                 data-frames='[{"delay":1200,"speed":2000,"frame":"0","from":"y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;","to":"o:1;","ease":"Power4.easeInOut"},
                                 {"delay":"wait","speed":300,"frame":"999","to":"auto:auto;","ease":"Power3.easeInOut"}]'

                                 data-textAlign="['center','center','center','center']"
                                 data-paddingtop="[0,0,0,0]"
                                 data-paddingright="[0,0,0,0]"
                                 data-paddingbottom="[0,0,0,0]"
                                 data-paddingleft="[0,0,0,0]"

                                 style="z-index: 5; white-space: nowrap; text-transform: capitalize;"><?php echo __('Kajiado eDevelopment Management System').'<br/>'.__('Online County Service Portal') ?></div>

                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
<?php
}
?>
        <section class="featureSection2">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3">
                        <div class="icon_box_01 text-center box_shadow">
                            <i class="bigger icofont-under-construction-alt"></i>
                            <i class="smaller icofont-under-construction-alt"></i>
                            <span></span>
                            <a href="#building"><h3>Business License permit<br/> Approval</h3></a>
                            <p>
                                Apply for a Business Permit
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3">
                        <div class="icon_box_01 text-center box_shadow">
                            <i class="bigger icofont-architecture"></i>
                            <i class="smaller icofont-architecture"></i>
                            <span></span>
                            <a href="subdiv"><h3>Liquor <br/>License</h3></a>
                            <p>
                                Apply for Liquor License
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3">
                        <div class="icon_box_01 text-center box_shadow">
                            <i class="bigger icofont-calculations"></i>
                            <i class="smaller icofont-calculations"></i>
                            <span></span>
                            <a href="#exten"><h3>Parking<br/>Daily/Weekly/Monthly</h3></a>
                            <p>
                                Apply Car Park Licenses 
                            </p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3">
                        <div class="icon_box_01 text-center box_shadow">
                            <i class="bigger icofont-safety-hat"></i>
                            <i class="smaller icofont-safety-hat"></i>
                            <span></span>
                            <a href="#inspec"><h3>Outdoor Advertising <br/> permit</h3></a>
                            <p>
                                Apply for Outdoor Advertising License to market your business
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3">
                        <h3>FlowEngine</h3>
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-9">
                        <h3>Nairobi County</h3>
                        <h4>Online County Service Portal<h4>
                    </div>
                </div>
            </div>
        </section>
        <section class="commonSection graySection2 pdb80">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 text-center">
                        <h6 class="sub_title gray_sub_title">Services</h6>
                        <h2 class="sec_title with_bar">
                            <span><span>Upcoming Services</span></span>
                        </h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                                <img src="/theme/images/construction/Civil.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2 id ="building">Construction approval</h2>
                                <p>A building/construction permit is an official approval issued by the local government agency that allows you or your contractor to proceed with a construction or remodeling project on your property.</p>
                                <p>It is intended to ensure that the project plans to comply with local standards for land use, zoning, and construction.</p>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/civil_blue.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2 id="subdiv">Subdivision approval</h2>
                                <p>Subbdivision approval is the offical approval given for the division of any land,other than buildings held under single ownership, into two or more parts.</p>
                                <p>Whether the subdivision is by conveyance, transfer or partition or for the purpose of sale, gift, lease or any other purpose.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/civil-engineer-640x230.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2>Consolidation approval</h2>
                                <p>
                                Consolidation approval is the offical approval given for the combination of two or more contiguous/adjoining lots of aliented land, land under separate titles</p>
                                <p>And are to be combined to a single title</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/Civil-Engineering-Tools.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2 id="exten">Extension/Renewal of<br/ >Lease approval</h2>
                                <p>
                                Extension/Renewal of Lease approval is the offical approval by the local government agency for leasehold</p>
                                <p>interest period extension/renewal.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/Civil-Enginnee.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2>Change of User approval</h2>
                                <p>
                                Change of User approval is the offical approval by the local government agency for the conversion of use of land</p> <p>By 50% or more besides that which has been approved, zoned or designated for the area
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/crane.jpg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2>Extension of User approval</h2>
                                <p>
                                Extension of User approval is the offical approval by the local government agency for adding to or expanding</p>
                                <p>The already permitted development rights to allow further changes in the use of land
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/plan/ner.png" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2 id="inspec">Construction Enforcement</h2>
                                <p>
                                Enforcement of constructions by the local government agency for development alleged to have been carried out without development permission, or the conditions of the development permission alleged to have been contravened
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="singleService">
                            <div class="serviceThumb">
                            <img src="/theme/images/construction/occupation_hardhat.jpeg" alt=""/>
                            </div>
                            <div class="serviceDetails">
                                <h2>Occupation approval</h2>
                                <p>
                                Occupation approval of a building for which a development permission had been</p>
                                <p> granted by the respective local authority
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>