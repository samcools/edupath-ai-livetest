(()=>{'use strict';
let recognition=null,listening=false;
const q=s=>document.querySelector(s);
function preferredVoice(lang){const voices=speechSynthesis?.getVoices?.()||[];const exact=voices.find(v=>v.lang?.toLowerCase()===(lang||'en-ZA').toLowerCase());const za=voices.find(v=>v.lang?.toLowerCase().endsWith('-za'));return exact||za||voices.find(v=>v.lang?.startsWith('en'))||voices[0]}
function speak(text,lang='en-ZA'){if(!('speechSynthesis'in window)||!text)return;speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(text);u.lang=lang;u.rate=.98;u.pitch=1;const v=preferredVoice(lang);if(v)u.voice=v;speechSynthesis.speak(u)}
function setupRecognition(){const SR=window.SpeechRecognition||window.webkitSpeechRecognition;if(!SR)return null;const r=new SR();r.continuous=false;r.interimResults=false;r.lang='en-ZA';r.maxAlternatives=1;r.onstart=()=>{listening=true;q('[data-ep-mic]')?.classList.add('listening');window.EduPathApp?.toast('Listening…')};r.onend=()=>{listening=false;q('[data-ep-mic]')?.classList.remove('listening')};r.onerror=()=>window.EduPathApp?.toast('Voice recognition is unavailable in this browser session.');r.onresult=e=>{const text=e.results?.[0]?.[0]?.transcript||'';if(text)window.EduPathApp?.executeText(text,true)};return r}
function listen(){if(!recognition)recognition=setupRecognition();if(!recognition){window.EduPathApp?.toast('Speech recognition is not supported by this browser. You can still type commands.');return}try{if(listening)recognition.stop();else recognition.start()}catch{}}
document.addEventListener('DOMContentLoaded',()=>{q('[data-ep-mic]')?.addEventListener('click',listen);if('speechSynthesis'in window)speechSynthesis.getVoices();});
window.EduPathVoice={speak,listen};
})();
