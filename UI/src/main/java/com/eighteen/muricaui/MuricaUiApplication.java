package com.eighteen.muricaui;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.domain.EntityScan;
import org.springframework.web.bind.annotation.RestController;

@SpringBootApplication
public class MuricaUiApplication {

	public static void main(String[] args) {
		SpringApplication.run(MuricaUiApplication.class, args);
	}

}
