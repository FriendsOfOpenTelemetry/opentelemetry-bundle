import{_ as i,c as o,b as p,a,d as e,e as t,f as l,r,o as d}from"./app-DAM_jpli.js";const c={};function u(m,n){const s=r("RouteLink");return d(),o("div",null,[n[6]||(n[6]=p(`<h1 id="getting-started" tabindex="-1"><a class="header-anchor" href="#getting-started"><span>Getting Started</span></a></h1><h2 id="installation" tabindex="-1"><a class="header-anchor" href="#installation"><span>Installation</span></a></h2><p>Run the following command to install it in your application:</p><div class="language-bash line-numbers-mode" data-highlighter="prismjs" data-ext="sh" data-title="sh"><pre><code><span class="line"><span class="token function">composer</span> require friendsofopentelemetry/opentelemetry-bundle</span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div></div></div><h3 id="supported-versions" tabindex="-1"><a class="header-anchor" href="#supported-versions"><span>Supported Versions</span></a></h3><p>There is no stable version yet, so you can use the <code>dev</code> version to install the bundle.</p><table><thead><tr><th>Version</th><th>Branch</th><th>PHP</th><th>OpenTelemetry</th><th>Symfony</th></tr></thead><tbody><tr><td>dev</td><td><code>main</code></td><td><code>^8.2</code></td><td><code>^1.0</code></td><td><code>^7.0</code></td></tr></tbody></table><h2 id="usage" tabindex="-1"><a class="header-anchor" href="#usage"><span>Usage</span></a></h2><p>This bundle is not yet available using Symfony Flex, so you have to manually configure it.</p><p>In your <code>config/bundles.php</code> file, add the following line at the end of the array:</p><div class="language-php line-numbers-mode" data-highlighter="prismjs" data-ext="php" data-title="php"><pre><code><span class="line"><span class="token keyword">return</span> <span class="token punctuation">[</span></span>
<span class="line">    <span class="token comment">// ...</span></span>
<span class="line">    <span class="token class-name class-name-fully-qualified static-context">FriendsOfOpenTelemetry<span class="token punctuation">\\</span>OpenTelemetryBundle</span><span class="token operator">::</span><span class="token keyword">class</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token string single-quoted-string">&#39;all&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"><span class="token punctuation">]</span><span class="token punctuation">;</span></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><p>Then, create a new file <code>config/packages/open_telemetry.yaml</code> and add the following minimal configuration:</p><div class="language-yaml line-numbers-mode" data-highlighter="prismjs" data-ext="yml" data-title="yml"><pre><code><span class="line"><span class="token key atrule">open_telemetry</span><span class="token punctuation">:</span></span>
<span class="line">  <span class="token key atrule">service</span><span class="token punctuation">:</span></span>
<span class="line">    <span class="token key atrule">namespace</span><span class="token punctuation">:</span> <span class="token string">&#39;MyCompany&#39;</span></span>
<span class="line">    <span class="token key atrule">name</span><span class="token punctuation">:</span> <span class="token string">&#39;MyApp&#39;</span></span>
<span class="line">    <span class="token key atrule">version</span><span class="token punctuation">:</span> <span class="token string">&#39;1.0.0&#39;</span></span>
<span class="line">    <span class="token key atrule">environment</span><span class="token punctuation">:</span> <span class="token string">&#39;%kernel.environment%&#39;</span></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div>`,13)),a("p",null,[n[1]||(n[1]=e("For further details on the configuration, please refer to the ")),t(s,{to:"/user-guide/configuration.html"},{default:l(()=>n[0]||(n[0]=[e("Configuration page")])),_:1}),n[2]||(n[2]=e("."))]),a("p",null,[n[4]||(n[4]=e("Next: ")),t(s,{to:"/instrumentation/introduction.html"},{default:l(()=>n[3]||(n[3]=[e("Instrumentation - Introduction")])),_:1}),n[5]||(n[5]=e("."))])])}const g=i(c,[["render",u],["__file","getting-started.html.vue"]]),v=JSON.parse('{"path":"/user-guide/getting-started.html","title":"Getting Started","lang":"en-US","frontmatter":{},"headers":[{"level":2,"title":"Installation","slug":"installation","link":"#installation","children":[{"level":3,"title":"Supported Versions","slug":"supported-versions","link":"#supported-versions","children":[]}]},{"level":2,"title":"Usage","slug":"usage","link":"#usage","children":[]}],"filePathRelative":"user-guide/getting-started.md","git":{"createdTime":1711217451000,"updatedTime":1711386927000,"contributors":[{"name":"Gaël Reyrol","username":"Gaël Reyrol","email":"me@gaelreyrol.dev","commits":2,"url":"https://github.com/Gaël Reyrol"}]}}');export{g as comp,v as data};
