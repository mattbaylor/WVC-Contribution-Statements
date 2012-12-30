USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_givingGreeting]    Script Date: 12/30/2012 11:39:43 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO







CREATE FUNCTION [dbo].[wvc_funct_givingGreeting]
(
	@GivingUnitID nvarchar(100)
)
RETURNS nvarchar(255)

AS
BEGIN
	DECLARE @FamilyID int
	SELECT @FamilyID = [ArenaDB].[dbo].[wvc_funct_familyIdFromGivingUnitId] (@GivingUnitID)
	DECLARE @Greeting nvarchar(255)
	IF (SELECT COUNT(cfm.person_id)
			FROM core_family_member cfm
				INNER JOIN core_person cp ON cfm.person_id = cp.person_id
			WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29 AND cp.record_status = 0 AND cp.contribute_individually = 0) <= 1
		BEGIN
			SELECT @Greeting = (
				SELECT TOP 1 
					CASE WHEN cp.first_name IS NULL OR LEN(cp.first_name) = 0 THEN
							[ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
						ELSE 
							CASE WHEN cpt.lookup_value IS NULL OR LEN(cpt.lookup_value) = 0 THEN
								[ArenaDB].[dbo].[wvc_funct_titleCase](cp.first_name) + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
							ELSE
								cpt.lookup_value + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.first_name) + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
							END
					END
				FROM core_family_member cfm
				INNER JOIN core_person cp ON cp.person_id = cfm.person_id
				LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
				LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
				LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
				WHERE cp.giving_unit_id = @GivingUnitID AND cfm.role_luid = 29 AND cp.record_status = 0
				ORDER BY cp.gender
				)
		END
	ELSE
		BEGIN
			SELECT @Greeting =  (SELECT TOP 1 cpt.lookup_value + ' and Mrs. ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.first_name) + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
			FROM core_family_member cfm
			INNER JOIN core_person cp ON cp.person_id = cfm.person_id
			LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
			LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
			LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
			WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29 AND cp.record_status = 0
			ORDER BY cp.gender)
		END
	RETURN (@Greeting)
END




GO

