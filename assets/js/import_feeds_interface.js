const importFeedsInterface = {
    path: feeds_interface_object.path,
    post: feeds_interface_object.post,
    root : 'import_rss_feed_interface',
    tmpStorage: {
        source_url: null,

    },
    style: {
        app: ['w-100','d-flex','flex-column','justify-content-between','position-relative'],
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
        },
        content: {
            main: ['w-100','d-flex','h-min-300px','justify-content-center','align-items-center'],
            source:{
                dynContent: ['p-5','border-radius-5px','border','min-w-300px','my-2','shadow-sm'],
                input: ['mt-5','p-2','w-100','border-radius-5px']
            },
        },
        neoNotify:{
            main: ['position-absolute','end-30px','top-30px','cursor-pointer'],
            notification:['text-white','p-3','border-radius-5px']
        },
    },
    init : ()=>{
        const appHolder = document.getElementById(importFeedsInterface.root).querySelector('.inside');
        const app = document.createElement('div');
        app.className = importFeedsInterface.style.app.join(' ');
        appHolder.replaceChildren(app);
        importFeedsInterface.source_page();
    },
    loader: ()=>{
        return '';
    },
    notify: async ({data,type})=>{
        const app = document.getElementById(importFeedsInterface.root).querySelector('.inside>div');
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
            next.addEventListener('click',()=>{
                const input = document.querySelector('[name="source_url"]');
                if(input.value.length != 0 && importFeedsInterface.isValidHttpUrl(input.value) ){
                    importFeedsInterface.tmpStorage['source_url'] = input.value;

                    importFeedsInterface.filter_page();
                }else{
                    importFeedsInterface.notify({data:'Please enter a valid url',type:'danger'});
                }
            });
            footer.append(next);
        }else if(step === 'filter'){
            next.innerText = 'Next';
            previous.innerText = 'Previous';
            previous.addEventListener('click',()=>{
                importFeedsInterface.source_page();
            });
            next.addEventListener('click',()=>{
                importFeedsInterface.assign_page();
            });
            footer.append(previous,next);
        }else if(step === 'assign'){
            next.innerText = 'Next';
            next.addEventListener('click',()=>{
                // save post
            });
            footer.append(previous,next);
        }
        
        return footer;
    },
    content: ({step})=>{
        const dynContent = document.createElement('div');
        dynContent.className = importFeedsInterface.style.content.source.dynContent.join(' ');
        if(step === 'source'){
            const title = document.createElement('h1');
            const subTitle = document.createElement('div');
            const input = document.createElement('input');
            input.name ='source_url';
            if(importFeedsInterface.tmpStorage['source_url']!==undefined){
                input.value = importFeedsInterface.tmpStorage['source_url'];
            }
            input.className = importFeedsInterface.style.content.source.input.join(' ');
            title.innerText = 'Feed Sources';
            subTitle.innerText = 'Provide valid source of the feed (i.e http://www.example.com/feed.xml)';
            dynContent.append(title,subTitle,input);
        }else if(step === 'filter'){

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
                const page = importFeedsInterface[`${options[i]}_page`];
                imgHolder.classList.remove('bg-inactive','cursor-not-allowed');
                imgHolder.classList.add('bg-active','cursor-pointer');
                imgHolder.addEventListener('click',function(){
                   page();
                });
            }
            const img = document.createElement('img');
            img.src = `${importFeedsInterface.path}assets/icons/${options[i]}_icon.png`;
            imgHolder.appendChild(img);
            steps.appendChild(imgHolder);
        }
        return steps;
    },
    source_page: ()=>{
        const step = 'source';
        const appHolder = document.getElementById(importFeedsInterface.root).querySelector('.inside');
        const app = document.createElement('div');
        app.className = importFeedsInterface.style.app.join(' ');
        const steps = importFeedsInterface.steps({step});
        const header = importFeedsInterface.header();
        const content = document.createElement('div');
        content.className = importFeedsInterface.style.content.main.join(' ');
        content.append(importFeedsInterface.content({step}));
        const footer = importFeedsInterface.footer({step});
        app.replaceChildren(header,steps,content,footer);
        appHolder.replaceChildren(app);
    },
    filter_page: ()=>{
        const step = 'filter';
        const appHolder = document.getElementById(importFeedsInterface.root).querySelector('.inside');
        const app = document.createElement('div');
        app.className = importFeedsInterface.style.app.join(' ');
        const steps = importFeedsInterface.steps({step});
        const header = importFeedsInterface.header();
        const content = document.createElement('div');
        content.className = importFeedsInterface.style.content.main.join(' ');
        content.append(importFeedsInterface.content({step}));
        const footer = importFeedsInterface.footer({step});
        app.replaceChildren(header,steps,content,footer);
        appHolder.replaceChildren(app);
    },
    assign_page: ()=>{
        const step = 'assign';
        const appHolder = document.getElementById(importFeedsInterface.root).querySelector('.inside');
        const app = document.createElement('div');
        app.className = importFeedsInterface.style.app.join(' ');
        const steps = importFeedsInterface.steps({step});
        const header = importFeedsInterface.header();
        const content = document.createElement('div');
        content.className = importFeedsInterface.style.content.main.join(' ');
        content.append(importFeedsInterface.content({step}));
        const footer = importFeedsInterface.footer({step});
        app.replaceChildren(header,steps,content,footer);
        appHolder.replaceChildren(app);
    },
    sleep: async (ms)=>new Promise(resolve => setTimeout(resolve, ms)),
};
importFeedsInterface.init();

console.log(importFeedsInterface.post);
