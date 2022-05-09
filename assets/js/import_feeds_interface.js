const importFeedsInterface = {
    path: feeds_interface_object.path,
    // meta_data: feeds_interface_object.meta_data,
    ajax_url: feeds_interface_object.ajax_url,
    ajax_nonce: feeds_interface_object.ajax_nonce,
    root : 'import_rss_feed_interface_app',
    formInputs: {
        source_url: feeds_interface_object.meta_data.source_url,
        include_text: feeds_interface_object.meta_data.include_text,
        exclude_text: feeds_interface_object.meta_data.exclude_text,
    },
    style: {
        app: ['w-100m20px','d-flex','flex-column','justify-content-between','position-relative','m-10px'],
        steps: {
            main: ['w-100','h-50px','d-flex','justify-content-around','position-relative'],
            imgHolder: ['shadow','cursor-not-allowed','w-50px','h-50px','bg-inactive','rounded','d-flex','justify-content-center','align-items-center','flex-column','position-relative','z-index-normal'],
        },
        header: {
            main: ['mb-3','p-2'],
        },
        footer:{
            main: ['d-flex','justify-content-end'],
            next: ['btn','bg-active','ml-3'],
            previous: ['btn','bg-inactive'],
            save: ['btn','bg-success','ml-3'],
        },
        content: {
            main: ['w-100','d-flex','justify-content-left','align-items-center'],
            source:{
                dynContent: ['my-2','p-2'],
                input: ['mt-3','p-2','w-100','border-radius-5px']
            },
            filter:{
                dynContent: ['my-2','p-2','w-100'],
                input: ['p-2','w-100','border-radius-5px'],
                subTitle: ['mt-3','pb-2','fs-13']
            },
        },
        neoNotify:{
            main: ['position-absolute','end-30px','top-30px','cursor-pointer'],
            notification:['text-white','p-3','border-radius-5px']
        },
        loader:{
            main: ['position-absolute','h-100p20px','w-100p20px','mn-10px','zindex-modal-backdrop','d-flex','justify-content-center','align-items-center','flex-column','bg-backdrop'],
            loader: ['border-solid','border-5px','border-rounded','border-top-warning','border-end-success','border-bottom-danger','border-start-primary','h-60px','w-60px','animate-spin-linear-infinite-1s']
        }
    },
    init : ()=>{
        const app = document.getElementById(importFeedsInterface.root);
        app.className = importFeedsInterface.style.app.join(' ');
        importFeedsInterface.page({step:'source'});
    },
    loader: (set)=>{
        if(set){
            const app = document.getElementById(importFeedsInterface.root);
            const content = document.createElement('div');
            content.id = 'auto_feeds_loader'
            content.className = importFeedsInterface.style.loader.main.join(' ');
            const loader = document.createElement('div');
            loader.className = importFeedsInterface.style.loader.loader.join(' ');
            content.appendChild(loader);
            app.appendChild(content);
        }else{
            const loader = document.getElementById('auto_feeds_loader');
            if(loader){
                loader.remove();
            }
        }
    },
    notify: async ({data,type})=>{
        const app = document.getElementById(importFeedsInterface.root);
        const neoNotify = document.createElement('div');
        neoNotify.onclick = ()=>{
            neoNotify.remove();
        }
        neoNotify.className = importFeedsInterface.style.neoNotify.main.join(' ');
        const notification = document.createElement('div');
        notification.className = importFeedsInterface.style.neoNotify.notification.join(' ');
        notification.classList.add(`bg-${type}`);
        notification.innerText = data;
        neoNotify.appendChild(notification);
        app.appendChild(neoNotify);
        await importFeedsInterface.sleep(1200);
        
        neoNotify.remove();
    },
    isValidHttpUrl:(string)=>{
        let url;
        try {url = new URL(string);} catch (_) {return false;}
        return url.protocol === "http:" || url.protocol === "https:";
    },
    footer: ({step})=>{
        const footer = document.createElement('div');
        footer.className = importFeedsInterface.style.footer.main.join(' ');
        const next = document.createElement('button');
        next.type ='button';
        next.className = importFeedsInterface.style.footer.next.join(' ');
        const previous = document.createElement('button');
        previous.type ='button';
        previous.className = importFeedsInterface.style.footer.previous.join(' ');
        if(step === 'source'){
            next.innerText = 'Next';
            next.addEventListener('click',async()=>{
                importFeedsInterface.loader(true);
                const app = document.getElementById(importFeedsInterface.root);
                const input = app.querySelector('[name="source_url"]');
                if(input.value.length != 0 && importFeedsInterface.isValidHttpUrl(input.value) ){
                    importFeedsInterface.formInputs.source_url = input.value;

                    const formData = new FormData();
                    formData.append( 'action', 'get_rss_content' );
                    formData.append( 'source_url', importFeedsInterface.formInputs.source_url );
                    formData.append( 'import_feeds_interface_nonce', importFeedsInterface.ajax_nonce );
                    fetch(importFeedsInterface.ajax_url,{
                        headers: {'Accept': 'application/json'},
                        method: 'POST',
                        body:formData,
                    }).then(res=>{
                        // console.log(res);
                        return res.json();
                    }).then(data=>{
                        console.log(data);
                        importFeedsInterface.rss_contents = data;
                        if(data.status === 'success'){
                            importFeedsInterface.page({step:'filter'});
                        }else{
                            importFeedsInterface.notify({data:data.message,type:data.status});
                        }
                    }).catch(err=>{
                        importFeedsInterface.notify({data:'Eror while processing the request',type:'error'});
                        console.log(err);
                    }).finally(()=>{
                        importFeedsInterface.loader(false);
                    });
                }else{
                    importFeedsInterface.notify({data:'Please enter a valid url',type:'danger'});
                }
            });
            footer.append(next);
        }else if(step === 'filter'){
            next.innerText = 'Next';
            previous.innerText = 'Previous';
            previous.addEventListener('click',()=>{
                
                importFeedsInterface.page({step:'source'});
            });
            next.addEventListener('click',()=>{
                const app = document.getElementById(importFeedsInterface.root);
                const include = app.querySelector('[name="include_text"]');
                const exclude = app.querySelector('[name="exclude_text"]');
                importFeedsInterface.formInputs.include_text = include.value;
                importFeedsInterface.formInputs.exclude_text = exclude.value;
                importFeedsInterface.page({step:'assign'});
            });
            footer.append(previous,next);
        }else if(step === 'assign'){
            next.innerText = 'Save';
            next.type = 'publish';
            next.name = 'publish'; 
            next.className = importFeedsInterface.style.footer.save.join(' ');
            next.addEventListener('click',(event)=>{
                //update assign table values to formInputs
                const app = document.getElementById(importFeedsInterface.root);
                event.preventDefault();
                importFeedsInterface.page({step:'assign'});
                const keys = Object.keys(importFeedsInterface.formInputs);
                keys.forEach(key=>{
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = importFeedsInterface.formInputs[key];
                    app.appendChild(input);
                });
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'publish';
                input.value = 'publish';
                app.appendChild(input);
                const form  = document.getElementById('post');
                form.submit();
            });
            footer.append(previous,next);
        }
        
        return footer;
    },
    content: ({step})=>{
        const dynContent = document.createElement('div');
        if(step === 'source'){
            dynContent.className = importFeedsInterface.style.content.source.dynContent.join(' ');
            const title = document.createElement('h1');
            const subTitle = document.createElement('div');
            const input = document.createElement('input');
            input.name ='source_url';
            if(importFeedsInterface.formInputs['source_url']!==undefined){
                input.value = importFeedsInterface.formInputs['source_url'];
            }
            input.className = importFeedsInterface.style.content.source.input.join(' ');
            title.innerText = 'Feed Sources';
            subTitle.innerText = 'Provide valid source of the feed (i.e http://www.example.com/feed.xml)';
            dynContent.append(title,subTitle,input);
        }else if(step === 'filter'){
            dynContent.className = importFeedsInterface.style.content.filter.dynContent.join(' ');
            const title = document.createElement('h1');
            const subTitle = document.createElement('span');
            const include = document.createElement('input');
            const exclude = document.createElement('input');
            const includeSubTitle = document.createElement('div');
            const excludeSubTitle = document.createElement('div');
            title.innerText = 'Feed Filters ';
            subTitle.innerText = `Detected ${importFeedsInterface.rss_contents.contentType} feeds from the source( ${importFeedsInterface.rss_contents.content.length} posts)`;
            subTitle.className = importFeedsInterface.style.content.filter.subTitle.join(' ');
            title.append(subTitle);
            include.name = 'include_text';
            exclude.name = 'exclude_text';
            include.placeholder = 'eg. news,sports etc';
            exclude.placeholder = 'eg. for sale,buy now etc';
            include.value = importFeedsInterface.formInputs.include_text;
            exclude.value = importFeedsInterface.formInputs.exclude_text;
            include.className = importFeedsInterface.style.content.filter.input.join(' ');
            exclude.className = importFeedsInterface.style.content.filter.input.join(' ');
            includeSubTitle.innerText = 'Only display items if title contains specific keywords (comma-separated/case sensitive)';
            excludeSubTitle.innerText = 'Exclude items if title contains specific keywords (comma-separated/case sensitive)';
            includeSubTitle.className = importFeedsInterface.style.content.filter.subTitle.join(' ');
            excludeSubTitle.className = importFeedsInterface.style.content.filter.subTitle.join(' ');
            dynContent.append(title,includeSubTitle,include,excludeSubTitle,exclude);
        }else if(step === 'assign'){
            
        }
        return dynContent;
    },
    header: ()=>{
        const header = document.createElement('div');
        header.className = importFeedsInterface.style.header.main.join(' ');
        const title = document.createElement('h1');
        const subtitle = document.createElement('span');
        title.innerText = 'Import Setup Interface';
        subtitle.innerText = 'Please follow through these steps to set import rule';
        header.append(title,subtitle);
        return header;
    },
    steps: ({step})=>{
        const steps = document.createElement('div');
        steps.className = importFeedsInterface.style.steps.main.join(' ');
        const options = ['source','filter','assign']
        for(let i=0; i<options.length; i++){
            const imgHolder = document.createElement('div');
            imgHolder.className = importFeedsInterface.style.steps.imgHolder.join(' ');
            if(options[i] === step){
                steps.classList.add(`pseudo-line-${i}`);
            }
            if(options.indexOf(step) >= i){
                imgHolder.classList.remove('bg-inactive','cursor-not-allowed');
                imgHolder.classList.add('bg-active','cursor-pointer');
                imgHolder.addEventListener('click',function(){
                    importFeedsInterface.page({step:options[i]});
                });
            }
            const img = document.createElement('img');
            img.src = `${importFeedsInterface.path}assets/icons/${options[i]}_icon.png`;
            imgHolder.appendChild(img);
            steps.appendChild(imgHolder);
        }
        return steps;
    },
    page: ({step})=>{
        const app = document.getElementById(importFeedsInterface.root);
        app.className = importFeedsInterface.style.app.join(' ');
        const steps = importFeedsInterface.steps({step});
        const header = importFeedsInterface.header();
        const content = document.createElement('div');
        content.className = importFeedsInterface.style.content.main.join(' ');
        content.append(importFeedsInterface.content({step}));
        const footer = importFeedsInterface.footer({step});
        app.replaceChildren(header,steps,content,footer);
    },
    sleep: async (ms)=>new Promise(resolve => setTimeout(resolve, ms)),
};
importFeedsInterface.init();
