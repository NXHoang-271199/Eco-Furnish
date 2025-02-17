@extends('layouts.admin')

@section('title')
    Chi tiết bài viết
@endsection



@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Chi tiết bài viết</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Bài Viết</a></li>
                        <li class="breadcrumb-item active">Chi tiết bài viết</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <p class="text-success text-uppercase mb-2">Art &amp; Design</p>
                        <h4 class="mb-2">The Art of Storytelling in Design</h4>
                        <p class="text-muted mb-4">How to create impactful narratives through visual elements.</p>
                        <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                            <span class="badge bg-primary-subtle text-primary">CraftedPerspectives</span>
                            <span class="badge bg-primary-subtle text-primary">DesignInspiration</span>
                            <span class="badge bg-primary-subtle text-primary">ArtAndDesign</span>
                        </div>
                    </div>
                    <img src="assets/images/blog/overview.jpg" alt="" class="img-thumbnail">

                    <div class="row mt-4">
                        <div class="col-lg-3">
                            <h6 class="pb-1">Contributor By:</h6>
                            <div class="d-flex gap-2 mb-3">
                                <div class="flex-shrink-0">
                                     <img src="assets/images/users/avatar-2.jpg" alt="" class="avatar-sm rounded">
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><a href="#!">Nancy Martino</a></h5>
                                    <p class="mb-2">Creative Designer</p>
                                    <p class="text-muted mb-0">2 hours ago</p>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-lg-9">
                            <p class="text-muted mb-3">Storytelling has always been a powerful way to communicate, and in the world of design, it’s no different. Visual storytelling allows designers to go beyond aesthetics and create meaningful connections with their audience. It transforms static designs into engaging narratives that leave a lasting impression. In this post, we’ll explore the art of storytelling in design, how it enhances user experiences, and how you can incorporate it into your creative work.</p>
                            <h6>Introduction:</h6>
                            <p class="text-muted mb-3">Design is more than just making things look good—it’s about conveying a message and creating an experience. And the best designs tell a story. From logos and branding to websites and product design, storytelling is woven into every aspect of successful design projects. When you integrate storytelling into your design, you give your audience something to connect with emotionally, making your work more memorable and impactful.</p>
                            <h5 class="fw-semibold">1. Why Storytelling Matters in Design:</h5>
                            <p class="text-muted mb-2">At its core, storytelling in design helps to create a deeper emotional connection between the user and the product or brand. It provides context, helps communicate values, and drives engagement by making the user feel like they’re part of the narrative. Here are a few reasons why storytelling is a vital part of design:</p>
                            <ul>
                                <li class="pb-1"><strong>Emotional Connection:</strong> <span class="text-muted">Stories evoke emotions, and emotions drive decisions. Whether you're designing a brand or a user interface, the emotions tied to your design influence how users engage with it.</span></li>
                                <li class="pb-1"><strong>Memorability:</strong> <span class="text-muted">People are more likely to remember a story than a simple image or message. By embedding a narrative into your design, you help your audience recall your brand, product, or message more effectively.</span></li>
                                <li><strong>Clarity of Message:</strong> <span class="text-muted">Good storytelling clarifies your message. It can take a complex idea and distill it into something easily digestible through visuals, layout, and interactions.</span></li>
                            </ul>
                            <blockquote class="blockquote custom-blockquote blockquote-secondary rounded mb-3">
                                <p class="text-body mb-2">"Storytelling in design turns a simple visual into an experience. It transforms static elements into living narratives that engage, inspire, and resonate with those who interact with them."</p>
                                <footer class="blockquote-footer mt-0"><cite title="Source Title">Liam Huxley</cite></footer>
                            </blockquote>
                            <h5 class="fw-semibold">2. Elements of Storytelling in Design:</h5>
                            <p class="text-muted mb-2">To tell a story through design, you need to incorporate several key elements:</p>
                            <ul>
                                <li class="pb-1"><strong>Characters:</strong> <span class="text-muted">Every story has characters, and in design, your characters might be users, brand mascots, or even products themselves. Think about how your design can personify or represent these characters to engage your audience.</span></li>
                                <li class="pb-1"><strong>Plot:</strong> <span class="text-muted">Your design should have a logical flow, leading the viewer through a narrative. This could be the journey of interacting with a product, the evolution of a brand, or even the progression of scrolling through a website.</span></li>
                                <li class="pb-1"><strong>Conflict &amp; Resolution:</strong> <span class="text-muted">A good story includes a challenge and how it’s overcome. In design, this might be highlighting a problem your product solves or guiding users through a process that resolves a pain point.</span></li>
                                <li><strong>Setting:</strong> <span class="text-muted">Visual design creates the “world” in which your story takes place. Think about how color, typography, and layout set the tone, mood, and context for your narrative.</span></li>
                            </ul>
                            <h5 class="fw-semibold">3. Techniques to Incorporate Storytelling in Design:</h5>
                            <ul>
                                <li class="pb-1"><strong>Consistent Visual Language:</strong> <span class="text-muted">Develop a cohesive visual language that aligns with the story you’re telling. This includes color schemes, typography, and iconography that reflect the brand’s identity and the message being conveyed.</span></li>
                                <li class="pb-1"><strong>Journey Mapping:</strong> <span class="text-muted">Especially in user interface design, the user’s journey is a key aspect of storytelling. Map out how users interact with your product or website and craft that journey to be intuitive, engaging, and narrative-driven.</span></li>
                                <li class="pb-1"><strong>Imagery and Metaphor:</strong> <span class="text-muted">Use images and icons to reinforce your story. Visual metaphors are powerful tools for conveying complex ideas simply. For example, a tree might represent growth, while a bridge can symbolize connection.</span></li>
                                <li class="pb-1"><strong>Microinteractions:</strong> <span class="text-muted">Small animations and interactions can add a dynamic layer to your story. Think about how users react to certain actions, like hovering over a button or scrolling through content. These interactions should feel natural and enhance the narrative.</span></li>
                                <li><strong>Typography as a Voice:</strong> <span class="text-muted">The font you choose can help set the tone for your story. Whether it’s bold and loud for a dramatic message or elegant and minimal for something more refined, typography plays a major role in how your story is perceived.</span></li>
                            </ul>
                            <h5 class="fw-semibold">4. Examples of Storytelling in Design:</h5>
                            <ul>
                                <li class="pb-1"><strong>Apple:</strong> <span class="text-muted">Apple is known for weaving storytelling into its product designs and marketing campaigns. Their product pages often take the user on a journey, explaining not just what the product is but how it fits into their lives, solving problems they didn’t even know they had.</span></li>
                                <li class="pb-1"><strong>Airbnb:</strong> <span class="text-muted">Airbnb uses storytelling extensively through user-generated content, allowing travelers to share their personal stories. Their website design reflects this, highlighting real people, real places, and the unique experiences that come with using their service.</span></li>
                                <li><strong>Nike:</strong> <span class="text-muted">Nike’s use of storytelling in design is legendary. Their branding, from product design to advertisements, is centered around stories of athletes overcoming obstacles, which mirrors their tagline, “Just Do It.” The design reflects these stories through bold imagery and inspiring copy.</span></li>
                            </ul>
                            <div>
                                <h5 class="fw-semibold mb-3">Comments:</h5>
                                <div data-simplebar="init" style="height: 300px;" class="px-3 mx-n3 mb-2 simplebar-scrollable-y"><div class="simplebar-wrapper" style="margin: 0px -16px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: 100%; overflow: hidden scroll;"><div class="simplebar-content" style="padding: 0px 16px;">
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <img src="assets/images/users/avatar-8.jpg" alt="" class="avatar-xs rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="fs-13">Joseph Parker <small class="text-muted ms-2">20 Dec 2021 - 05:47AM</small></h5>
                                            <p class="text-muted">I am getting message from customers that when they place order always get error message .</p>
                                            <a href="javascript: void(0);" class="badge text-muted bg-light"><i class="mdi mdi-reply"></i> Reply</a>
                                            <div class="d-flex mt-4">
                                                <div class="flex-shrink-0">
                                                    <img src="assets/images/users/avatar-10.jpg" alt="" class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="fs-13">Alexis Clarke <small class="text-muted ms-2">22 Dec 2021 - 02:32PM</small></h5>
                                                    <p class="text-muted">Please be sure to check your Spam mailbox to see if your email filters have identified the email from Dell as spam.</p>
                                                    <a href="javascript: void(0);" class="badge text-muted bg-light"><i class="mdi mdi-reply"></i> Reply</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <img src="assets/images/users/avatar-6.jpg" alt="" class="avatar-xs rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="fs-13">Donald Palmer <small class="text-muted ms-2">24 Dec 2021 - 05:20PM</small></h5>
                                            <p class="text-muted">If you have further questions, please contact Customer Support from the “Action Menu” on your <a href="javascript:void(0);" class="text-decoration-underline">Online Order Support</a>.</p>
                                            <a href="javascript: void(0);" class="badge text-muted bg-light"><i class="mdi mdi-reply"></i> Reply</a>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="assets/images/users/avatar-10.jpg" alt="" class="avatar-xs rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="fs-13">Alexis Clarke <small class="text-muted ms-2">26 min ago</small></h5>
                                            <p class="text-muted">Your <a href="javascript:void(0)" class="text-decoration-underline">Online Order Support</a> provides you with the most current status of your order. To help manage your order refer to the “Action Menu” to initiate return, contact Customer Support and more.</p>
                                            <div class="row g-2 mb-3">
                                                <div class="col-lg-1 col-sm-2 col-6">
                                                    <img src="assets/images/small/img-4.jpg" alt="" class="img-fluid rounded">
                                                </div>
                                                <div class="col-lg-1 col-sm-2 col-6">
                                                    <img src="assets/images/small/img-5.jpg" alt="" class="img-fluid rounded">
                                                </div>
                                            </div>
                                            <a href="javascript: void(0);" class="badge text-muted bg-light"><i class="mdi mdi-reply"></i> Reply</a>
                                            <div class="d-flex mt-4">
                                                <div class="flex-shrink-0">
                                                    <img src="assets/images/users/avatar-6.jpg" alt="" class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="fs-13">Donald Palmer <small class="text-muted ms-2">8 sec ago</small></h5>
                                                    <p class="text-muted">Other shipping methods are available at checkout if you want your purchase delivered faster.</p>
                                                    <a href="javascript: void(0);" class="badge text-muted bg-light"><i class="mdi mdi-reply"></i> Reply</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div></div></div></div><div class="simplebar-placeholder" style="width: 1134px; height: 579px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="height: 155px; transform: translate3d(0px, 0px, 0px); display: block;"></div></div></div>
                                <form class="mt-4">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="inputName" class="form-label text-body">Name</label>
                                            <input class="form-control bg-light border-light" id="inputName" rows="3" placeholder="Enter your name" required="">
                                        </div>
                                        <div class="col-12">
                                            <label for="exampleFormControlTextarea1" class="form-label text-body">Leave a Comments</label>
                                            <textarea class="form-control bg-light border-light" id="exampleFormControlTextarea1" rows="3" placeholder="Enter your comment..." required=""></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon waves-effect me-1"><i class="ri-attachment-line fs-16"></i></button>
                                            <a href="javascript:void(0);" class="btn btn-success">Post Comments</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                </div>
            </div>
        </div>
    </div>

</div>
@endsection