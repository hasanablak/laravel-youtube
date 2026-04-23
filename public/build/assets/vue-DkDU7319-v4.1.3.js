import{j as d,k as a,N as u,l as E,p as g,q as h,E as f}from"./@vue-Bq8VMgKq-v4.1.3.js";/**
* vue v3.4.31
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/const l=new WeakMap;function C(e){let n=l.get(e??f);return n||(n=Object.create(null),l.set(e??f,n)),n}function T(e,n){if(!a(e))if(e.nodeType)e=e.innerHTML;else return u;const o=e,t=C(n),s=t[o];if(s)return s;if(e[0]==="#"){const c=document.querySelector(e);e=c?c.innerHTML:""}const r=E({hoistStatic:!0,onError:void 0,onWarn:u},n);!r.isCustomElement&&typeof customElements<"u"&&(r.isCustomElement=c=>!!customElements.get(c));const{code:m}=g(e,r),i=new Function("Vue",m)(h);return i._rc=!0,t[o]=i}d(T);
