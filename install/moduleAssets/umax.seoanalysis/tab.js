document.addEventListener('DOMContentLoaded', function(){
    let errorTitles = document.querySelectorAll('#seoOnPage .error-element__title')
    for (let i = 0; i < errorTitles.length; i++) {
        errorTitles[i].addEventListener('click', function() {
            let content = errorTitles[i].parentNode.querySelector('.error-element__content')
            content.classList.toggle('active')
            errorTitles[i].classList.toggle('active')
        })
    }

    let detail = document.querySelector('#seoOnPage #detail')
    let name = document.querySelector('input[name="NAME"]').value
    if(detail) {
        let curType = detail.getAttribute('type');
        let url = false;
        if(curType == 'GOODS')
            url = '/bitrix/admin/umax_global_analysis_ajax_detail_goods.php'
        else if(curType == 'SERVICE')
            url = '/bitrix/admin/umax_global_analysis_ajax_detail_services.php';
        else if(curType == 'NEWS')
            url = '/bitrix/admin/umax_global_analysis_ajax_detail_news.php'

        if(url) {
            detail.addEventListener('click', function () {
                document.querySelector('.seoOnPage .loading').classList.add('active')
                document.body.style.overflow = 'hidden'
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        page: window.location.origin + detail.getAttribute('url'),
                        type: curType,
                        name: name,
                        id: detail.getAttribute('data-id')
                    }
                }).then(res => {
                    location.reload()
                })
            })
        }
    }
    const anchors = document.querySelectorAll('.header__info .info__status')

    for (let anchor of anchors) {
        anchor.addEventListener('click', function (e) {
            e.preventDefault()
            
            const blockID = anchor.getAttribute('href').substr(1)
            
            let elem = document.getElementById(blockID)
            elem.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'start'
            })
        })
    }

    let scroll = document.querySelector('.seoOnPage__scroll')
    scroll?.addEventListener('click', function() {
        let start = document.querySelector('.adm-workarea')
        start.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        })
    })

    window.addEventListener('scroll', function() {
        if(window.scrollY > 350) {
            if(!scroll.classList.contains('active'))
                scroll.classList.add('active')
        } else {
            scroll.classList.remove('active')
        }
    })
})