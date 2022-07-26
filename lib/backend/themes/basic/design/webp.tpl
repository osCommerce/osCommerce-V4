<style type="text/css">
    .preloader {
        max-width: 300px;
        display: none;
    }
    .value {
        font-weight: bold;
    }
    .buttons {
        margin-bottom: 20px;
    }
    .no-support {
        color: #f00;
        margin-bottom: 20px
    }
    .countItems {
        display: none;
    }
    .progress{
        width: 400px;
        height: 20px;
        margin-bottom: 20px;
        display: none;
    }
    .progress .progress-bar {
        width: 0;
        height: 20px;
        background: #305c88;
    }
    .product-images, .category-images, .banner-images {
        margin-bottom: 10px;
    }
</style>
{if !$imagewebp}
    <div class="no-support">{$smarty.const.SERVER_DOEST_SUPPORT_WEBP_FORMAT}</div>
{/if}
<div class="" style="min-height: 500px">
    <div class="buttons">
        <span class="btn create-webp" data-type="products">{$smarty.const.TEXT_PRODUCT_IMAGES}</span>
        <span class="btn create-webp" data-type="categories">{$smarty.const.TEXT_CATEGORY_IMAGES}</span>
        <span class="btn create-webp" data-type="banners">{$smarty.const.TEXT_BANNERS_IMAGES}</span>

        {foreach $buttonSettings as $settings}
            <span class="btn create-webp" data-type="{$settings.type}">{$settings.buttonTitle}</span>
        {/foreach}

        <span class="btn create-webp" data-type="">{$smarty.const.TEXT_ALL_IMAGES}</span>

    </div>

    <div class="countItems all-products">{$smarty.const.TEXT_PROCESSED_PRODUCTS}: <span class="value"></span></div>
    <div class="countItems all-products-images">{$smarty.const.TEXT_PROCESSED_PRODUCTS_IMAGES}: <span class="value"></span></div>
    <div class="countItems product-images">{$smarty.const.TEXT_CREATED_NEW_PRODUCT_IMAGES}: <span class="value"></span></div>
    <div class="progress product-progress-bar"><div class="progress-bar"></div></div>

    <div class="countItems all-categories">{$smarty.const.TEXT_PROCESSED_CATEGORIES}: <span class="value"></span></div>
    <div class="countItems all-categories-images">{$smarty.const.TEXT_PROCESSED_CATEGORIES_IMAGES}: <span class="value"></span></div>
    <div class="countItems category-images">{$smarty.const.TEXT_CREATED_NEW_CATEGORY_IMAGES}: <span class="value"></span></div>
    <div class="progress category-progress-bar"><div class="progress-bar"></div></div>

    <div class="countItems all-banners">{$smarty.const.TEXT_PROCESSED_BANNERS}: <span class="value"></span></div>
    <div class="countItems all-banners-images">{$smarty.const.TEXT_PROCESSED_BANNERS_IMAGES}: <span class="value"></span></div>
    <div class="countItems banner-images">{$smarty.const.TEXT_CREATED_NEW_BANNER_IMAGES}: <span class="value"></span></div>
    <div class="progress banner-progress-bar"><div class="progress-bar"></div></div>

    {foreach $buttonSettings as $settings}
        {foreach $settings.progressData as $name => $title}
            <div class="countItems {$settings.type}-{$name}">{$title}: <span class="value"></span></div>
        {/foreach}
        <div class="progress {$settings.type}-progress-bar"><div class="progress-bar"></div></div>
    {/foreach}

    <div class="preloader"></div>
</div>


<script>
    $(function(){
        const buttons = $('.create-webp');
        const preloader = $('.preloader');
        const countItems = $('.countItems');
        const progress = $('.progress');
        const progressBar = $('.progress-bar', progress);

        const allProducts = $('.all-products');
        const allProductsValue = $('.value', allProducts);
        const allProductsImages = $('.all-products-images');
        const allProductsImagesValue = $('.value', allProductsImages);
        const productImages = $('.product-images');
        const productImagesValue = $('.value', productImages);
        const productProgress = $('.product-progress-bar');
        const productProgressBar = $('.product-progress-bar .progress-bar');

        const allCategories = $('.all-categories');
        const allCategoriesValue = $('.value', allCategories);
        const allCategoriesImages = $('.all-categories-images');
        const allCategoriesImagesValue = $('.value', allCategoriesImages);
        const categoryImages = $('.category-images');
        const categoryImagesValue = $('.value', categoryImages);
        const categoryProgress = $('.category-progress-bar');
        const categoryProgressBar = $('.category-progress-bar .progress-bar');

        const allBanners = $('.all-banners');
        const allBannersValue = $('.value', allBanners);
        const allBannersImages = $('.all-banners-images');
        const allBannersImagesValue = $('.value', allBannersImages);
        const bannerImages = $('.banner-images');
        const bannerImagesValue = $('.value', bannerImages);
        const bannerProgress = $('.banner-progress-bar');
        const bannerProgressBar = $('.banner-progress-bar .progress-bar');


        buttons.on('click', function(){
            preloader.show();
            countItems.hide();
            $('.value', countItems).text('');
            progressBar.css('width', 0);
            progress.hide();

            let arrays = ['products', 'categories', 'banners'];
            {foreach $buttonSettings as $settings}
            arrays.push('{$settings.type}');
            {/foreach}

            if ($(this).data('type')){
                arrays = [$(this).data('type')];
            }
            arrays.reduce(
                (p, type) => p.then( () => new Promise(
                    resolve => sendQuery(type, 0, () => resolve())
                )),
                Promise.resolve()
            ).then(function() {
                preloader.hide();
            });

        });


        function sendQuery(type, iteration, end){
            $.get('design/create-webp', { type: type, iteration: iteration }, function(response){
                updateFields(response);

                if (!response.iteration || response.iteration == iteration) {
                    end();
                } else {
                    sendQuery(type, response.iteration, end)
                }
            }, 'json')
        }


        function updateFields(response){
            if (response.product !== undefined) {
                productProgress.show();
                allProducts.show();
                allProductsValue.text(1 * allProductsValue.text() +  1 * response.product)
            }
            if (response.product_images !== undefined) {
                productImages.show();
                productImagesValue.text(1 * productImagesValue.text() +  1 * response.product_images)
            }
            if (response.product_images_all !== undefined) {
                allProductsImages.show();
                allProductsImagesValue.text(1 * allProductsImagesValue.text() +  1 * response.product_images_all)
            }
            if (response.product_images_all !== undefined && response.products_count) {
                productProgressBar.css('width', allProductsImagesValue.text() * 400 / response.products_count)
            }

            if (response.categories !== undefined) {
                categoryProgress.show();
                allCategories.show();
                allCategoriesValue.text(1 * allCategoriesValue.text() +  1 * response.categories);
            }
            if (response.category_images !== undefined) {
                categoryImages.show();
                categoryImagesValue.text(1 * categoryImagesValue.text() +  1 * response.category_images)
            }
            if (response.category_images_all !== undefined) {
                allCategoriesImages.show();
                allCategoriesImagesValue.text(1 * allCategoriesImagesValue.text() +  3 * response.category_images_all)
            }
            if (response.category_images_all !== undefined && response.categories_count) {
                categoryProgressBar.css('width', allCategoriesImagesValue.text() * 400 / response.categories_count)
            }

            if (response.banners !== undefined) {
                bannerProgress.show();
                allBanners.show();
                allBannersValue.text(1 * allBannersValue.text() +  1 * response.banners)
            }
            if (response.banner_images !== undefined) {
                bannerImages.show();
                bannerImagesValue.text(1 * bannerImagesValue.text() +  1 * response.banner_images)
            }
            if (response.banner_images_all !== undefined) {
                allBannersImages.show();
                allBannersImagesValue.text(1 * allBannersImagesValue.text() +  1 * response.banner_images_all);
                bannerProgressBar.css('width', 400)
            }


            {foreach $buttonSettings as $settings}
                {foreach $settings.progressData as $name => $title}
                    if (response['{$settings.type}'] && response['{$settings.type}']['{$name}'] !== undefined) {
                        $('.{$settings.type}-{$name}').show();
                        $('.{$settings.type}-{$name} .value').text((+$('.{$settings.type}-{$name} .value').text()) + (+response['{$settings.type}']['{$name}']))
                    }
                {/foreach}

                if (response['{$settings.type}'] && response['{$settings.type}'].progress) {
                    $('.{$settings.type}-progress-bar').show();
                    $('.{$settings.type}-progress-bar .progress-bar').css('width', response['{$settings.type}'].progress * 4)
                }
            {/foreach}
        }
    })
</script>
