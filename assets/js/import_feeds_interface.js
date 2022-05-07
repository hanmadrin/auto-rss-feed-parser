const importFeedsInterface = {
    path: feeds_interface_object.path,
    root : 'import_rss_feed_interface',
    style: {
        app: ['w-100','d-flex','flex-column','justify-content-between'],
        steps: {
            main: ['w-100','h-50px','d-flex','justify-content-around','position-relative'],
            imgHolder: ['shadow','cursor-not-allowed','w-50px','h-50px','bg-inactive','rounded','d-flex','justify-content-center','align-items-center','flex-column','position-relative','z-index-normal'],
        },
        header: {
            main: ['mb-3','p-2'],
        },
        footer:{
            main: ['d-flex','justify-content-end'],
            button: ['btn','bg-active'],
        },
        content: {
            main: ['w-100','d-flex','h-min-300px','justify-content-center','align-items-center'],
            source:{
                dynContent: ['p-5','border-radius-5px','border','min-w-300px','my-2','shadow-sm'],
                input: ['mt-5','p-2','w-100','border-radius-5px']
            },
        },
        neoNotify:{
            main: ['position-fixed','end-30px','top-30px','cursor-pointer'],
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
        document.body.appendChild(neoNotify);
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
        const button = document.createElement('button');
        button.type ='button';
        button.className = importFeedsInterface.style.footer.button.join(' ');
        if(step === 'source'){
            button.innerText = 'Next';
            button.addEventListener('click',()=>{
                const input = document.querySelector('[name="auto-feeds-source_url"]');
                if(input.value.length != 0 && importFeedsInterface.isValidHttpUrl(input.value) ){
                    importFeedsInterface.filter_page();
                }else{
                    importFeedsInterface.notify({data:'Please enter a valid url',type:'danger'});
                }
            });
        }else if(step === 'filter'){
            button.innerText = 'Next';
            button.addEventListener('click',()=>{
                importFeedsInterface.assign_page();
            });
        }else if(step === 'assign'){
            button.innerText = 'Next';
            button.addEventListener('click',()=>{
                // save post
            });
        }
        footer.append(button);
        return footer;
    },
    content: ({step})=>{
        const dynContent = document.createElement('div');
        dynContent.className = importFeedsInterface.style.content.source.dynContent.join(' ');
        if(step === 'source'){
            const title = document.createElement('h1');
            const subTitle = document.createElement('div');
            const input = document.createElement('input');
            input.name ='auto-feeds-source_url';
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

