@extends('layouts.twh')

@section('title', 'Contact Us | ' . SystemHelper::appName())

@section('content')

    <!-- Contact Section Begin -->
    <section class="contact-section spad">
        <div class="container">
            <div class="row">
                <!-- Contact Info -->
                <div class="col-lg-4">
                    <div class="contact-text">
                        <h2>Contact Info</h2>
                        <p>{{ 'Reach out to us for inquiries, bookings, or support.' }}</p>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="c-o">Address:</td>
                                    <td>{{ SystemHelper::address() }}</td>
                                </tr>
                                <tr>
                                    <td class="c-o">Phone:</td>
                                    <td>{{ SystemHelper::contactPhone() }}</td>
                                </tr>
                                <tr>
                                    <td class="c-o">Email:</td>
                                    <td>{{ SystemHelper::contactEmail() }}</td>
                                </tr>
                            </tbody>
                        </table>
                        {{-- <div class="fa-social mt-3">
                            @foreach(SystemHelper::socials() as $social)
                                <a href="{{ $social['link'] }}" target="_blank"><i class="fa {{ $social['icon'] }}"></i></a>
                            @endforeach
                        </div> --}}
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-7 offset-lg-1">
                    <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="text" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="email" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="col-lg-12">
                                <textarea name="message" placeholder="Your Message" required></textarea>
                                <button type="submit">Submit Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Google Map -->
            <div class="map">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.455772147304!2d35.33669591415503!3d-0.7724088354978684!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182b992800de585b%3A0x215bc9ef0ea8c6f9!2sThe%20Willis%20Hotel!5e0!3m2!1sen!2ske!4v1628355866093!5m2!1sen!2ske" 
						height="470" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->
@endsection
