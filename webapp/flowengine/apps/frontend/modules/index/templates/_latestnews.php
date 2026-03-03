<?php
use_helper('I18N');

$q = Doctrine_Query::create()
   ->from('News a')
   ->where('a.published = ?', 1)
   ->orderBy('a.id DESC')
   ->limit(5);
$news=$q->execute();
?>
<section class="commonSection pdb115">
	<div class="container">
		<div class="row">
			<div class="col-xl-12 text-center">
				<h6 class="sub_title dark_sub_title">Blog</h6>
				<h2 class="sec_title">
					<span>News Feeds</span>
				</h2>
			</div>
		</div>
		<?php foreach($news as $n): ?> 
		<div class="row">
			<div class="col-xl-12 col-lg-12">
				<div class="blogItem2 bIcollapse">
						<div class="bi_meta">
							<span><i class="fal fa-calendar-alt"></i><a href="#"><?php echo date('jS M, Y',strtotime($n->getCreatedOn())) ?></a></span>
							<!--span><i class="fal fa-comments"></i><a href="#">43 Comments</a></span-->
						</div>
						<h3>
						<a href="<?php echo url_for('/plan/news/article/id/'.$n->getId()) ?>"><?php echo html_entity_decode($n->getTitle()) ?></a>
						</h3>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</section>